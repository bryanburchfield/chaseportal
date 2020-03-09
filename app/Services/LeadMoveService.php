<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\Includes\PowerImportAPI;
use App\Models\LeadRule;
use App\Models\User;
use App\Models\Dialer;
use App\Models\LeadMove;
use App\Models\LeadMoveDetail;
use App\Traits\SqlServerTraits;
use App\Traits\TimeTraits;
use Illuminate\Support\Carbon;

use Illuminate\Support\Facades\DB;

class LeadMoveService
{
    use SqlServerTraits;
    use TimeTraits;

    private $api;
    private $today;

    public function __construct()
    {
        $this->today = date('Y-m-d');
    }

    /**
     * Run Filter
     *
     * This is executed from schedule to perform lead moves
     *
     * @return void
     */

    public static function runFilter()
    {
        $leadmover = new LeadMoveService();

        $lead_rules = LeadRule::where('active', 1)
            ->orderBy('group_id')
            ->orderBy('rule_name', 'desc')
            ->get();

        $batch = [];
        // This inserts them into the log table first
        foreach ($lead_rules as $lead_rule) {
            $batch[] = $leadmover->runRule($lead_rule);
        }

        // And this does the actual moves
        foreach ($batch as $lead_move_id) {
            $leadmover->filterLeads($lead_move_id);
        }
    }

    /**
     * Reverse Move
     *
     * This is executed from job queue to reverse a move
     *
     * @param LeadMove $lead_move
     * @return void
     */
    public function reverseMove(LeadMove $lead_move)
    {
        $lead_rule = LeadRule::find($lead_move->lead_rule_id);
        $lead_move_details = LeadMoveDetail::where('lead_move_id', $lead_move->id)->get();

        foreach ($lead_move_details as $lead_move_detail) {

            $detail = new \stdClass();
            $detail->reporting_db = $lead_move_detail->reporting_db;
            $detail->lead_id = $lead_move_detail->lead_id;
            $detail->group_id = $lead_rule->group_id;
            $detail->destination_campaign = $lead_rule->source_campaign;
            $detail->destination_subcampaign = $lead_rule->source_subcampaign;

            $api = $this->initApi($detail->reporting_db);

            $this->filterLead($api, $detail);
        }
    }

    public function runRule(LeadRule $lead_rule)
    {
        if (!Auth::check() || Auth::user()->group_id !== $lead_rule->group_id) {
            // authenticate as user of the group
            if (Auth::check()) {
                Auth::logout();
            }
            $user = User::where('group_id', '=', $lead_rule->group_id)->first();

            if ($user) {
                // set a flag so the audit trail doesn't pick it up
                $user->cron = true;
                Auth::login($user);
            }
        }

        // make sure we actually logged someone in
        if (Auth::check()) {
            // create the batch record
            $lead_move = LeadMove::create([
                'lead_rule_id' => $lead_rule->id,
            ]);

            foreach (Auth::user()->getDatabaseList() as $db) {
                $this->pullLeads($lead_move->id, $db, $lead_rule);
            }
            return $lead_move->id;
        }
        return 0;
    }

    private function pullLeads($lead_move_id, $db, $lead_rule)
    {
        echo "running rule " . $lead_rule->id . "\n";

        // find all leads that match the rule
        // insert/update them in the log
        // this way if a single lead matches multiple rules
        // we don't try to move it more than once
        foreach ($this->getLeads($db, $lead_rule) as $lead) {
            DB::table('lead_move_details')
                ->updateOrInsert(
                    [
                        'reporting_db' => $db,
                        'lead_id' => $lead['id'],
                        'succeeded' => null,
                    ],
                    [
                        'lead_move_id' => $lead_move_id,
                        'created_at' =>  Carbon::now(),
                    ]
                );
        }
    }

    private function filterLeads($lead_move_id)
    {
        // cursor thru the log and do the moves
        foreach (LeadMoveDetail::where('lead_move_id', $lead_move_id)
            ->where('succeeded', null)
            ->join('lead_moves', 'lead_moves.id', '=', 'lead_move_details.lead_move_id')
            ->join('lead_rules', 'lead_rules.id', '=', 'lead_moves.lead_rule_id')
            ->select(
                'lead_move_details.*',
                'lead_rules.group_id',
                'lead_rules.destination_campaign',
                'lead_rules.destination_subcampaign'
            )
            ->get() as $detail) {
            $api = $this->initApi($detail->reporting_db);
            $detail->succeeded = $this->filterLead($api, $detail);
            $detail->save();
        }
    }

    private function filterLead($api, $detail)
    {
        echo "Moving Lead: " . $detail->lead_id .
            " for group " . $detail->group_id .
            " to " . $detail->destination_campaign .
            "/" . $detail->destination_subcampaign .
            "\n";
        $data['Campaign'] = $detail->destination_campaign;
        $data['Subcampaign'] = $detail->destination_subcampaign;
        $result = $api->UpdateDataByLeadId($data, $detail->group_id, '', '', $detail->lead_id);
        if ($result === false) {
            return false;
        }
        return true;
    }

    private function getLeads($db, $lead_rule)
    {
        $sql = "SELECT id, Subcampaign FROM [$db].[dbo].[Leads]
        WHERE GroupId = :group_id
        AND Campaign = :campaign";

        $bind = [
            'group_id' => Auth::user()->group_id,
            'campaign' => $lead_rule->source_campaign,
        ];

        if (!empty($lead_rule->source_subcampaign)) {
            $sql .= " AND Subcampaign = :subcampaign";
            $bind['subcampaign'] = $lead_rule->source_subcampaign;
        }

        $i = 0;
        foreach ($lead_rule->leadRuleFilters as $lead_rule_filter) {
            $i++;
            switch ($lead_rule_filter->type) {
                case 'lead_attempts':
                    $sql .= " AND Attempt >= :param$i";
                    break;
                case 'lead_age':
                    $sql .= $this->sqlAge($i);
                    break;
                case 'days_called':
                    $sql .= $this->sqlDay($i, $db);
                    break;
                default:  // error!
                    return [];
            }

            $bind['param$i'] = $lead_rule_filter->value;
        }

        $leads = $this->runSql($sql, $bind);

        // if source & dest camps are the same, filter out any where subcamp already matches
        if ($lead_rule->source_campaign === $lead_rule->destination_campaign) {
            $newleads = [];

            foreach ($leads as $lead) {
                if ($lead['Subcampaign'] != $lead_rule->destination_subcampaign) {
                    $newleads[] = $lead;
                }
            }
            $leads = $newleads;
        }

        return $leads;
    }

    private function sqlAge($i)
    {
        $date = $this->localToUtc(date('Y-m-d'), Auth::user()->iana_tz)
            ->format('Y-m-d H:i:s');

        return " AND '$date' - Date > :param$i";
    }

    private function sqlDay($i, $db)
    {
        return " AND (SELECT COUNT(DISTINCT CONVERT(date, Date))
            FROM [$db].[dbo].[DialingResults]
            WHERE GroupId = Leads.GroupId
            AND LeadId = Leads.Id) > :param$i";
    }

    private function initApi($db)
    {
        if (empty($this->api[$db])) {
            $fqdn = Dialer::where('reporting_db', $db)->pluck('dialer_fqdn')->first();
            $this->api[$db] = new PowerImportAPI('http://' . $fqdn . '/PowerStudio/WebAPI');
        }
        return $this->api[$db];
    }
}
