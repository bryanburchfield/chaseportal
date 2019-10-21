<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\Includes\PowerImportAPI;
use App\LeadRule;
use App\User;
use App\Dialer;
use App\LeadMove;
use App\Traits\SqlServerTraits;
use Illuminate\Support\Facades\DB;

/**
 * Lead Move Service
 *
 * Run from command line (ideally cron)
 * php artisan command:dump_leads {group_id} {timezone} {database}
 * eg: php artisan command:dump_leads 224347 America/New_York PowerV2_Reporting_Dialer-17
 *
 * NOTE: .env must contain the following keys for each client:
 *   FTP_HOST_{group_id}
 *   FTP_USERNAME_{group_id}
 *   FTP_PASSWORD_{group_id}
 *   FTP_EMAIL_{group_id}
 *
 * and config/filesystems.php must contain for each client:
 * 'disks' => [
 *       'ftp_{group_id}' => [
 *           'driver' => 'ftp',
 *           'host' => env('FTP_HOST_{group_id}'),
 *           'username' => env('FTP_USERNAME_{group_id}'),
 *           'password' => env('FTP_PASSWORD_{group_id}'),
 *           'email' => env('FTP_EMAIL_{group_id}'),
 *           'root' => '/',
 *        ],
 *   ]
 */
class LeadMoveService
{
    private $api;
    private $today;

    use SqlServerTraits;

    public function __construct()
    {
        $this->today = date('Y-m-d');
    }

    public static function runMove()
    {
        $leadmover = new LeadMoveService();

        $rules = LeadRule::where('active', 1)
            ->orderBy('group_id')
            ->orderBy('rule_name', 'desc')
            ->get();

        // This inserts them into the log table first
        foreach ($rules as $rule) {
            $leadmover->runRule($rule);
        }

        // And this does the actual moves
        $leadmover->moveLeads();
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
            foreach (Auth::user()->getDatabaseList() as $db) {
                $this->pullLeads($db, $lead_rule);
            }
        }
    }

    private function pullLeads($db, $lead_rule)
    {
        echo "running rule " . $lead_rule->id . "\n";

        // find all leads that match the rule
        // insert/update them in the log
        foreach ($this->getLeads($db, $lead_rule) as $lead) {
            DB::table('lead_moves')
                ->updateOrInsert(
                    [
                        'reporting_db' => $db,
                        'lead_id' => $lead['id'],
                        'run_date' => $this->today,
                    ],
                    ['lead_rule_id' => $lead_rule->id, 'succeeded' => false]
                );
        }
    }

    private function moveLeads()
    {
        // cursor thru the log and do the moves
        // this way if a single lead matches multiple rules
        // we don't try to move it more than once
        foreach (LeadMove::where('run_date', $this->today)
            ->where('succeeded', false)
            ->join('lead_rules', 'lead_rules.id', '=', 'lead_moves.lead_rule_id')
            ->select(
                'lead_moves.*',
                'lead_rules.group_id',
                'lead_rules.destination_campaign',
                'lead_rules.destination_subcampaign'
            )
            ->get() as $lead_move) {

            $api = $this->initApi($lead_move->reporting_db);

            if ($this->moveLead($api, $lead_move)) {
                $lead_move->succeeded = true;
                $lead_move->save();
            }
        }
    }

    private function moveLead($api, $lead_move)
    {
        echo "Moving Lead: " . $lead_move->lead_id .
            " for group " . $lead_move->group_id .
            " to " . $lead_move->destination_campaign .
            "/" . $lead_move->destination_subcampaign .
            "\n";

        $data['Campaign'] = $lead_move->destination_campaign;
        $data['Subcampaign'] = $lead_move->destination_subcampaign;

        // $result = $api->UpdateDataByLeadId($data, $lead_move->group_id, '', '', $lead_move->lead_id);
        // if ($result === false) {
        //     return false;
        // }

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
        $date = localToUtc(date('Y-m-d'), Auth::user()->getIanaTz())
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
