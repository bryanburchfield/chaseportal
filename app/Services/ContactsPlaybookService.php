<?php

/////////////////////////////
// look for TODO's!!!!
/////////////////////////////

namespace App\Services;

use App\Includes\PowerImportAPI;
use App\Models\Campaign;
use App\Models\ContactsPlaybook;
use App\Models\ContactsPlaybookAction;
use App\Models\Dialer;
use App\Models\EmailServiceProvider;
use App\Models\PlaybookEmailAction;
use App\Models\PlaybookFilter;
use App\Models\PlaybookOptout;
use App\Models\PlaybookRun;
use App\Models\PlaybookRunDetail;
use App\Models\Script;
use App\Models\User;
use App\Traits\SqlServerTraits;
use App\Traits\TimeTraits;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client as Twilio;
use InvalidArgumentException;

class ContactsPlaybookService
{
    use SqlServerTraits;
    use TimeTraits;

    private $powerImportApis = [];
    private $twilio;

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
                // TODO:  dispatch this to run in the background
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

        // Log the run
        $playbook_run = PlaybookRun::create(['contacts_playbook_id' => $contacts_playbook->id]);

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

            // Log the detail
            PlaybookRunDetail::create([
                'playbook_run_id' => $playbook_run->id,
                'playbook_action_id' => $contacts_playbook_action->playbook_action_id,
                'reporting_db' => $db,
                'lead_id' => $rec['lead_id'],
            ]);
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
        $result = false;

        switch ($contacts_playbook_action->playbook_action->action_type) {
            case 'lead':
                $result = $this->actionLead($contacts_playbook_action, $rec);
                break;
            case 'email':
                $result = $this->actionEmail($contacts_playbook_action, $rec);
                break;
            case 'sms':
                $result = $this->actionSms($contacts_playbook_action, $rec);
                break;
        }
    }

    private function actionLead(ContactsPlaybookAction $contacts_playbook_action, $rec)
    {
        $playbook_action = $contacts_playbook_action->playbook_action;

        $api = $this->initApi(Auth::user()->db);

        $data = [];

        if (!empty($playbook_action->playbook_lead_action->to_campaign)) {
            $data['Campaign'] = $playbook_action->playbook_lead_action->to_campaign;
        }
        if (!empty($playbook_action->playbook_lead_action->to_subcampaign)) {
            $data['Subcampaign'] = $playbook_action->playbook_lead_action->to_subcampaign;
        }
        if (!empty($playbook_action->playbook_lead_action->to_callstatus)) {
            $data['CallStatus'] = $playbook_action->playbook_lead_action->to_callstatus;
        }

        echo "Moving Lead: " . $rec['lead_id'] . "\n";
        dump($data);

        // $result = $api->UpdateDataByLeadId($data, Auth::user()->group_id, '', '', $rec['lead_id']);

        return true;
    }

    private function actionEmail(ContactsPlaybookAction $contacts_playbook_action, $rec)
    {
        echo "Email Lead: " . $rec['lead_id'] . "\n";

        $playbook_action = $contacts_playbook_action->playbook_action;

        // If email field is blank, bail now
        $email = $rec[$playbook_action->playbook_email_action->email_field];
        if ($email == 'NULL' || empty($email)) {
            return false;
        }

        // Check if they opted-out
        if (
            PlaybookOptout::where('group_id', Auth::user()->group_id)
            ->where('email', $email)
            ->count()
            > 0
        ) {
            return false;
        }

        // Get history of sends for this lead for this playbook & action
        $sends = $this->getHistory($contacts_playbook_action, $rec['lead_id']);

        // Bail if over limit or under days between
        if ($sends->isNotEmpty()) {
            if ($sends->count() >= $playbook_action->emails_per_lead) {
                return false;
            }

            if (!empty($playbook_action->days_between_emails)) {
                if ($sends->last()->created_at->diffInDays() < $playbook_action->days_between_emails) {
                    return false;
                }
            }
        }

        // ok to send
        return $this->emailLead($contacts_playbook_action->contacts_playbook, $playbook_action->playbook_email_action, $rec);
    }

    private function actionSms(ContactsPlaybookAction $contacts_playbook_action, $rec)
    {
        echo "SMS Lead: " . $rec['lead_id'] . "\n";

        $playbook_action = $contacts_playbook_action->playbook_action;

        // Check for phone number
        if (empty($rec['PrimaryPhone'])) {
            return false;
        }

        // Get history of sends for this lead for this playbook & action
        $sends = $this->getHistory($contacts_playbook_action, $rec['lead_id']);

        // Bail if over limit or under days between
        if ($sends->isNotEmpty()) {
            if ($sends->count() >= $playbook_action->sms_per_lead) {
                return false;
            }

            if (!empty($playbook_action->days_between_sms)) {
                if ($sends->last()->created_at->diffInDays() < $playbook_action->days_between_sms) {
                    return false;
                }
            }
        }

        $body = $this->mergeTemplate($playbook_action->playbook_sms_action->template_id, $playbook_action->campaign, $rec);

        if ($body === false) {
            return false;
        }

        // Init Twilio if not already
        if (empty($this->twilio)) {
            $this->initTwilio();
        }



        // TODO:  REMOVE AFTER TESTING
        $rec['PrimaryPhone'] = '3212629660';


        echo "sending message\n";
        // try {
        $message = $this->twilio->messages->create(
            $rec['PrimaryPhone'],
            [

                // TODO: from number has to be from a select list

                // 'from' => $playbook_action->playbook_sms_action->from,
                'from' => '+15617258677',

                'body' => $body,
            ]
        );
        dump($message);
        // } catch (Exception $e) {
        //     echo "SMS Failed\n";
        //     return false;
        // }

        return true;
    }

    private function emailLead(ContactsPlaybook $contacts_playbook, PlaybookEmailAction $playbook_email_action, $rec)
    {
        // Get body and subject merged with rec
        $body = $this->mergeTemplate($playbook_email_action->template_id, $contacts_playbook->campaign, $rec);
        $subject = $this->mergeFields($playbook_email_action->subject, $contacts_playbook->campaign, $rec);

        if ($body === false) {
            return false;
        }

        // build payload
        $payload = [
            'from' => $playbook_email_action->from,



            // TODO: REMOVE AFTER TESTING
            // 'to' => $rec[$playbook_email_action->email_field],
            'to' => 'g.sandoval@chasedatacorp.com',



            'subject' => $subject,
            'body' => $body,
            'tag' => $contacts_playbook->name,
        ];

        // find ESP model
        $email_service_provider = EmailServiceProvider::find($playbook_email_action->email_service_provider_id);
        if (!$email_service_provider) {
            return false;
        }

        // instantiate ESP interface
        $class = $email_service_provider->providerClassName();
        $email_service_provider = new $class($email_service_provider);

        // Fire!
        $result = $email_service_provider->send($payload);

        return true;
    }

    private function mergeTemplate($template_id, $campaign, $rec)
    {
        // load body from template
        $script = Script::find($template_id);
        if (!$script) {
            return false;
        }

        return $this->mergeFields($script->HtmlContent, $campaign, $rec);
    }

    private function mergeFields($text, $campaign, $rec)
    {
        // get list of mergable fields
        if (!empty($campaign)) {
            $campaign = Campaign::where('GroupId', Auth::user()->group_id)
                ->where('CampaignName', $campaign)
                ->first();
        } else {
            $campaign = new Campaign;
        }

        $fields = array_keys($campaign->getFilterFields());

        // do merge
        foreach ($fields as $field) {
            $text = str_ireplace('(#' . $field . '#)', htmlspecialchars($rec[$field]), $text);
        }

        return $text;
    }

    private function getHistory($contacts_playbook_action, $lead_id)
    {
        return PlaybookRun::where('contacts_playbook_id', $contacts_playbook_action->contacts_playbook_id)
            ->join('playbook_run_details', 'playbook_run_details.playbook_run_id', '=', 'playbook_runs.id')
            ->where('reporting_db', Auth::user()->db)
            ->where('lead_id', $lead_id)
            ->where('playbook_run_details.playbook_action_id', $contacts_playbook_action->playbook_action_id)
            ->select('playbook_run_details.created_at')
            ->orderBy('playbook_run_details.created_at')
            ->get();
    }

    private function initApi($db)
    {
        if (empty($this->powerImportApis[$db])) {
            $fqdn = Dialer::where('reporting_db', $db)->pluck('dialer_fqdn')->first();
            $this->powerImportApis[$db] = new PowerImportAPI('http://' . $fqdn . '/PowerStudio/WebAPI');
        }

        return $this->powerImportApis[$db];
    }
    private function initTwilio()
    {
        $sid    = config('twilio.sid');
        $token  = config('twilio.token');

        $this->twilio = new Twilio($sid, $token);
    }
}
