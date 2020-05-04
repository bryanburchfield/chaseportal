<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\ContactsPlaybook;
use App\Models\ContactsPlaybookAction;
use App\Models\PlaybookFilter;
use App\Models\User;
use App\Traits\SqlServerTraits;
use App\Traits\TimeTraits;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class ContactsPlaybookService
{
    use SqlServerTraits;
    use TimeTraits;

    private $powerImportApis = [];

    /**
     * Instantiate the class and off we go
     * 
     * @return void 
     * @throws InvalidArgumentException 
     */
    public static function execute()
    {
        $service = new ContactsPlaybookService();
        $service->runPlaybooks();
    }

    /**
     * Run all active playbooks
     * 
     * @return void 
     * @throws InvalidArgumentException 
     */
    public function runPlaybooks()
    {
        $contacts_playbooks = ContactsPlaybook::where('active', 1)
            ->orderBy('group_id')
            ->orderBy('name')
            ->get();

        foreach ($contacts_playbooks as $contacts_playbook) {
            if ($this->login($contacts_playbook->group_id)) {
                // should dispatch this to run in the background
                $this->runPlaybook($contacts_playbook);
            }
        }
    }

    /**
     * Login as first member of group, if not already
     * 
     * @param mixed $group_id 
     * @return bool 
     */
    private function login($group_id)
    {
        if (!Auth::check() || Auth::user()->group_id !== $group_id) {
            // authenticate as user of the group
            if (Auth::check()) {
                Auth::logout();
            }
            $user = User::where('group_id', '=', $group_id)->first();

            if ($user) {
                // set a flag so the audit trail doesn't pick it up
                session(['isCron' => 1]);
                Auth::login($user);
            }
        }

        // see if we actually logged someone in
        return Auth::check();
    }

    /**
     * Run a specific playbook
     * 
     * @param ContactsPlaybook $contacts_playbook 
     * @return void 
     * @throws InvalidArgumentException 
     * @throws Exception 
     */
    public function runPlaybook(ContactsPlaybook $contacts_playbook)
    {
        // update run times 
        $now = (new Carbon())->toDateTimeString();
        $contacts_playbook->last_run_from = empty($contacts_playbook->last_run_to) ? $now : $contacts_playbook->last_run_to;
        $contacts_playbook->last_run_to = $now;
        $contacts_playbook->save();

        // Set SqlSrv database
        $db = Auth::user()->db;
        config(['database.connections.sqlsrv.database' => $db]);

        // Get query
        list($sql, $bind) = $this->buildSql($contacts_playbook);

        Log::debug($sql);
        Log::debug($bind);

        $results = $this->runSql($sql, $bind);

        foreach ($results as $rec) {
            foreach ($contacts_playbook->actions as $contacts_playbook_action) {
                $this->runAction($contacts_playbook_action, $rec);
            }
        }
    }

    /**
     * Build sql and bind array based on playbook filters
     * 
     * @param ContactsPlaybook $contacts_playbook 
     * @return (string|array)[] 
     * @throws Exception 
     * @throws InvalidArgumentException 
     */
    private function buildSql(ContactsPlaybook $contacts_playbook)
    {
        // Set SqlSrv database
        $db = Auth::user()->db;
        config(['database.connections.sqlsrv.database' => $db]);

        // We'll need the campaign to get custom table fields
        $campaign = Campaign::where('CampaignName', $contacts_playbook->campaign)
            ->where('GroupId', Auth::user()->group_id)
            ->first();

        // Build WHERE clause from filters
        $where = '';
        $dr_where = '';  // for DialingResults subquery
        $bind = [
            'group_id' => Auth::user()->group_id,
            'campaign' => $contacts_playbook->campaign,
        ];

        foreach ($contacts_playbook->filters as $contacts_playbook_filter) {
            $playbook_filter = $contacts_playbook_filter->playbook_filter;

            $and = ' AND ' . $this->buildAnd($playbook_filter, $campaign, $bind);

            if ($playbook_filter->field == 'Call Status') {
                $dr_where .= $and;
            } else {
                $where .= $and;
            }
        }

        // query leads, outer join custom table
        $sql = "SELECT L.id AS lead_id, * FROM [$db].[dbo].[Leads] L";

        if (!empty($campaign->advancedTable)) {
            $sql .= " INNER JOIN [$db].[dbo].[ADVANCED_" . $campaign->advancedTable->TableName . "] A ON A.LeadId = L.IdGuid";
        }

        $sql .= " WHERE GroupId = :group_id
        AND Campaign = :campaign";

        if (!empty($contacts_playbook->subcampaign)) {
            $subcampaign = ($contacts_playbook->subcampaign == '!!none!!') ? '' : $contacts_playbook->subcampaign;

            $bind['subcampaign'] = $subcampaign;

            $sql .= " AND Subcampaign = :subcampaign";
        }

        $sql .= ' ' . $where . "
            AND L.id IN (
            SELECT LeadId FROM [$db].[dbo].[DialingResults] DR
            WHERE DR.Date > '$contacts_playbook->last_run_from'
            AND DR.Date <= '$contacts_playbook->last_run_to'
            $dr_where
            )";

        return [$sql, $bind];
    }

    /**
     * Build the 'AND' clause
     * 
     * @param PlaybookFilter $playbook_filter 
     * @param Campaign $campaign 
     * @param mixed $bind 
     * @return string 
     * @throws Exception 
     * @throws InvalidArgumentException 
     */
    private function buildAnd(PlaybookFilter $playbook_filter, Campaign $campaign, &$bind)
    {
        $today = Carbon::parse('midnight today', Auth::user()->iana_tz)->tz('UTC');
        $i = count($bind);

        switch ($playbook_filter->operator) {
            case 'blank':
                $compare = '= \'\'';
                break;
            case 'not_blank':
                $compare = '!= \'\'';
                break;
            case 'days_ago':
                $from = (clone $today)->subDays($playbook_filter->value)->toDateTimeString();
                $to = (clone $today)->subDays($playbook_filter->value - 1)->toDateTimeString();
                $compare = 'BETWEEN \'' . $from . '\' AND \'' . $to . '\'';
                break;
            case 'days_from_now':
                $from = (clone $today)->addDays($playbook_filter->value)->toDateTimeString();
                $to = (clone $today)->addDays($playbook_filter->value + 1)->toDateTimeString();
                $compare = 'BETWEEN \'' . $from . '\' AND \'' . $to . '\'';
                break;
            case '<_days_ago':
                $from = (clone $today)->subDays($playbook_filter->value)->toDateTimeString();
                $compare = '< \'' . $from . '\'';
                break;
            case '>_days_ago':
                $from = (clone $today)->subDays($playbook_filter->value - 1)->toDateTimeString();
                $compare = '>= \'' . $from . '\'';
                break;
            case '<_days_from_now':
                $from = (clone $today)->addDays($playbook_filter->value)->toDateTimeString();
                $compare = '< \'' . $from . '\'';
                break;
            case '>_days_from_now':
                $from = (clone $today)->addDays($playbook_filter->value + 1)->toDateTimeString();
                $compare = '>= \'' . $from . '\'';
                break;
            default:
                $compare = $playbook_filter->operator . ' :bind' . $i;
                $bind['bind' . $i] = $playbook_filter->value;
        }

        // create array of custom table fields
        if (empty($campaign->advancedTable->advancedTableFields)) {
            $custom_table_fields = [];
        } else {
            $custom_table_fields = array_column($campaign->advancedTable->advancedTableFields->toArray(), 'FieldName');
        }

        // Some fields are special
        if ($playbook_filter->field == 'Lead Age') {
            $where = $this->sqlAge($i);
        } elseif ($playbook_filter->field == 'Call Status') {
            $where = 'DR.CallStatus ' . $compare;
        } elseif ($playbook_filter->field == 'Attempts') {
            $where = "Attempt >= $compare";
        } elseif ($playbook_filter->field == 'Days Called') {
            $where = $this->sqlDay($i);
        } elseif ($playbook_filter->field == 'Ring Group') {
            $where = $this->sqlRingGroup($i);
        } elseif (in_array($playbook_filter->field, $custom_table_fields)) {
            $where = 'A.[' . $playbook_filter->field . '] ' . $compare;
        } else {
            $where = 'L.[' . $playbook_filter->field . '] ' . $compare;
        }

        return $where;
    }

    /**
     * Build 'AND' for and age comparison
     * 
     * @param mixed $i 
     * @return string 
     * @throws Exception 
     */
    private function sqlAge($i)
    {
        $date = $this->localToUtc(date('Y-m-d'), Auth::user()->iana_tz)
            ->format('Y-m-d H:i:s');

        return "DATEDIFF(day, Date, '$date') > :bind$i";
    }

    /**
     * Build 'AND' for a days comparison
     * 
     * @param mixed $i 
     * @return string 
     */
    private function sqlDay($i)
    {
        $db = Auth::user()->db;

        return "(SELECT COUNT(DISTINCT CONVERT(date, Date))
        FROM [$db].[dbo].[DialingResults]
        WHERE GroupId = Leads.GroupId
        AND LeadId = Leads.Id) > :bind$i";
    }

    /**
     * Build 'AND' subquery for ring group (inbound source)
     * 
     * @param mixed $i 
     * @return string 
     */
    private function sqlRingGroup($i)
    {
        $db = Auth::user()->db;

        return "EXISTS (SELECT I.id
            FROM [$db].[dbo].[DialingResults] DR
            INNER JOIN [$db].[dbo].[InboundSources] I ON I.InboundSource = DR.CallerId AND I.Description = :bind$i
            WHERE DR.GroupId = Leads.GroupId
            AND DR.LeadId = Leads.Id
            AND DR.CallType = 1
            AND DR.attempt = 1)";
    }

    /**
     * Run an action of a playbok on a lead
     * 
     * @param ContactsPlaybookAction $contacts_playbook_action 
     * @param mixed $rec 
     * @return void 
     */
    private function runAction(ContactsPlaybookAction $contacts_playbook_action, $rec)
    {
        Log::debug($contacts_playbook_action);
        Log::debug($rec);
    }
}
