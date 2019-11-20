<?php

namespace App\Services;

use App\DbLeadmove;
use Illuminate\Support\Facades\Auth;
use App\Includes\PowerImportAPI;
use App\LeadRule;
use App\User;
use App\Dialer;
use App\LeadMove;
use App\LeadMoveDetail;
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

    public function runRule(LeadRule $lead_rule)
    {
        if (!Auth::check() || Auth::user()->group_id !== $lead_rule->group_id) {
            // authenticate as user of the group
            Auth::logout();
            $user = User::where('group_id', '=', $lead_rule->group_id)->first();
            if ($user) {
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
        $sql = "SELECT id FROM [$db].[dbo].[Leads]
        WHERE GroupId = :group_id
        AND Campaign = :campaign ";

        switch ($lead_rule->filter_type) {
            case 'lead_attempts':
                $sql .= "AND Attempt >= :param";
                break;
            case 'lead_age':
                $sql .= $this->sqlAge($lead_rule);
                break;
            case 'days_called':
                $sql .= $this->sqlDay($lead_rule, $db);
                break;
            default:
                return [];
        }

        $bind = [
            'group_id' => Auth::user()->group_id,
            'param' => $lead_rule->filter_value,
            'campaign' => $lead_rule->source_campaign,
        ];

        if (!empty($lead_rule->source_subcampaign)) {
            $bind['subcampaign'] = $lead_rule->source_subcampaign;
        }

        return $this->runSql($sql, $bind);
    }

    private function sqlAge($lead_rule)
    {
        $date = $this->localToUtc(date('Y-m-d'), Auth::user()->iana_tz)
            ->format('Y-m-d H:i:s');

        return "AND '$date' - Date > :param";
    }

    private function sqlDay($lead_rule, $db)
    {
        return "AND (SELECT COUNT(DISTINCT CONVERT(date, Date))
            FROM [$db].[dbo].[DialingResults]
            WHERE GroupId = Leads.GroupId
            AND LeadId = Leads.Id) > :param";
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
