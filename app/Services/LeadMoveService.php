<?php

namespace App\Services;

use App\DbLeadmove;
use Illuminate\Support\Facades\Auth;
use App\Includes\PowerImportAPI;
use App\LeadRule;
use App\User;
use App\Dialer;
use App\LeadMove;
use App\Traits\SqlServerTraits;
use App\Traits\TimeTraits;
use Illuminate\Http\Request;
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
    use SqlServerTraits;
    use TimeTraits;

    private $api;
    private $today;

    public function __construct()
    {
        $this->today = date('Y-m-d');
    }

    public function storeLead(Request $request)
    {
        $db_leadmove = new DbLeadmove();
        $db_leadmove->lead_id = $request->lead;
        $db_leadmove->db_from = $request->source;
        $db_leadmove->db_to = $request->destination;
        $db_leadmove->save();
    }

    public static function moveLeads()
    {
        $leadmover = new LeadMoveService();
        $leadmover->startMove();
    }

    public function startMove()
    {
        $process_id = uniqid();

        // set a process_id on all the recs we're about to process
        DbLeadmove::whereNull('process_id')->update(['process_id' => $process_id]);
        foreach (DbLeadmove::where('process_id', $process_id)->get() as $db_leadmove) {
            $db_leadmove->succeeded = $this->moveLead($db_leadmove);
            $db_leadmove->save();
        }
    }

    private function moveLead(DbLeadmove $db_leadmove)
    {
        if ($this->uploadLead($db_leadmove)) {
            $this->updateSource($db_leadmove->lead_id, $db_leadmove->db_from);
            return true;
        }

        return false;
    }

    private function uploadLead(DbLeadmove $db_leadmove)
    {
        $leadData = $this->readLead($db_leadmove->lead_id, $db_leadmove->db_from);

        if (empty($leadData)) {
            echo "Can't find lead " . $db_leadmove->lead_id . "\n";
            return false;
        }
        if ($leadData['Campaign'] == '_Moved_') {
            echo "Already moved " . $db_leadmove->lead_id . "\n";
            return false;
        }

        $groupid = $leadData['GroupId'];
        $campaign = $leadData['Campaign'];
        $subcampaign = $leadData['Subcampaign'];

        $data['FirstName'] = $leadData['FirstName'];
        $data['LastName'] = $leadData['LastName'];
        $data['Address'] = $leadData['Address'];
        $data['City'] = $leadData['City'];
        $data['State'] = $leadData['State'];
        $data['ZipCode'] = $leadData['ZipCode'];
        $data['PrimaryPhone'] = $leadData['PrimaryPhone'];
        $data['Campaign'] = $leadData['Campaign'];
        $data['Subcampaign'] = $leadData['Subcampaign'];
        $data['Notes'] = $leadData['Notes'];

        $dialDups = 0;
        $dialNonCallables = 0;
        $duplicatesCheck = 1;

        $api = $this->initApi($db_leadmove->db_to);

        $result = $api->ImportData($data, $groupid, $campaign, $subcampaign, $dialDups, $dialNonCallables, $duplicatesCheck);

        if ($result === false) {
            return false;
        }

        return true;
    }

    private function readLead($lead_id, $db_from)
    {
        // get lead from reporting db

        $db = Dialer::where('dialer_numb', $db_from)->select('reporting_db')->first();
        $db = $db->reporting_db;

        $table = "[$db].[dbo].[Leads]";

        $sql = "SELECT
            GroupId,
            FirstName,
            LastName,
            Address,
            City,
            State,
            ZipCode,
            PrimaryPhone,
            Campaign,
            Subcampaign,
            Notes
        FROM $table WHERE id = :id";

        $bind['id'] = $lead_id;

        $data = $this->runSql($sql, $bind);
        if (count($data)) {
            $data = $data[0];
        }

        return $data;
    }

    private function updateSource($lead_id, $db_from)
    {
        $api = $this->initApi($db_from);

        $data['Campaign'] = '_Moved_';

        $result = $api->UpdateDataByLeadId($data, $this->leadData['GroupId'], '', '', $lead_id);

        if ($result === false) {
            return false;
        }

        return true;
    }

    public static function runFilter()
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
        $leadmover->filterLeads();
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

    private function filterLeads()
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

            if ($this->filterLead($api, $lead_move)) {
                $lead_move->succeeded = true;
                $lead_move->save();
            }
        }
    }

    private function filterLead($api, $lead_move)
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
