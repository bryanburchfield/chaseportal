<?php

namespace App\Http\Controllers;

use App\Http\Requests\LeadFilter;
use App\Jobs\ReverseLeadMove;
use App\Models\LeadMove;
use App\Models\LeadMoveDetail;
use App\Models\LeadRule;
use App\Mail\LeadDumpMail;
use App\Models\LeadRuleFilter;
use App\Traits\SqlServerTraits;
use App\Traits\CampaignTraits;
use App\Traits\TimeTraits;
use Exception;
use Illuminate\Database\Eloquent\JsonEncodingException;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\UrlGenerationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class LeadsController extends Controller
{
    use SqlServerTraits;
    use CampaignTraits;
    use TimeTraits;

    protected $db;

    /**
     * Get History (ajax)
     * @return array[]
     * @throws Exception 
     * @throws InvalidArgumentException 
     */
    public function getHistory()
    {
        $table = [];

        $lead_moves = LeadMove::where('lead_moves.created_at', '>', Carbon::parse('30 days ago'))
            ->join('lead_rules', 'lead_moves.lead_rule_id', '=', 'lead_rules.id')
            ->where('lead_rules.group_id', Auth::user()->group_id)
            ->select(
                'lead_moves.*',
                'lead_rules.rule_name'
            )
            ->OrderBy('lead_moves.id', 'desc')
            ->get();

        $tz = Auth::user()->iana_tz;
        foreach ($lead_moves as $lead_move) {
            $count = LeadMoveDetail::where('lead_move_id', $lead_move->id)->where('succeeded', true)->count();
            if ($count) {
                $table[] = [
                    'lead_move_id' => $lead_move->id,
                    'lead_rule_id' => $lead_move->lead_rule_id,
                    'date' => Carbon::parse($lead_move->created_at)->tz($tz)->isoFormat('L LT'),
                    'rule_name' => $lead_move->rule_name,
                    'leads_moved' => $count,
                    'reversed' => $lead_move->reversed,
                ];
            }
        }

        return $table;
    }

    /**
     * Reverse Move (ajax)
     * 
     * @param Request $request 
     * @return array[]
     */
    public function reverseMove(Request $request)
    {
        // Make sure we haven't already reversed it
        $lead_move = LeadMove::find($request->lead_move_id);
        if (!$lead_move || $lead_move->reversed) {
            return ['error' => 'Already reversed'];
        }

        // Make sure the user is allowed to reverse it
        $lead_rule = LeadRule::find($lead_move->lead_rule_id);
        if (!$lead_rule || $lead_rule->group_id != Auth::user()->group_id) {
            return ['error' => 'Not Authorized'];
        }

        // Set the record to reversed
        $lead_move->reversed = true;
        $lead_move->save();

        // Dispatch job to run the reverse in the background
        ReverseLeadMove::dispatch($lead_move);

        return ['success' => true];
    }

    /**
     * Get Campaigns (ajax)
     * 
     * @param Request $request 
     * @return array[] 
     * @throws InvalidArgumentException 
     */
    public function getCampaigns(Request $request)
    {
        $fromDate = $request->fromdate;
        $toDate = $request->todate;

        $results = $this->getAllCampaigns($fromDate, $toDate);

        return ['campaigns' => array_values($results)];
    }

    /**
     * Get Subcampaigns (ajax)
     * 
     * @param Request $request 
     * @return array[] 
     */
    public function getSubcampaigns(Request $request)
    {
        $results = $this->getAllSubcampaigns($request->campaign);

        return ['subcampaigns' => array_values($results)];
    }

    /**
     * Lead Dump
     * pull a file and dump to an ftp server
     *
     * @param Request $request
     * @return void
     */
    public function leadDump(Request $request)
    {
        // Check that we have the group configured in .env and config/filesystems.php
        $email = config('filesystems.disks.ftp_' . $request->group_id . '.email');

        if (empty($email)) {
            die('Unauthorized group.');
        }

        // columns we want
        $columns = [
            'L.id',
            'L.ClientId',
            'L.FirstName',
            'L.LastName',
            'L.Address',
            'L.City',
            'L.State',
            'L.ZipCode',
            'L.PrimaryPhone',
            'L.SecondaryPhone',
            'L.Rep',
            'L.CallStatus',
            'L.Date',
            'L.Campaign',
            'L.Attempt',
            'L.WasDialed',
            'L.LastUpdated',
            'L.Notes',
            'L.Subcampaign',
            'L.CallType',
            'L.FullName',
            'A.LID',
            'A.inputName',
            'A.inputEmail',
            'A.inputBill',
            'A.inputResidenceType',
            'A.inputOwnershiptype',
            'A.inputSquareFootage',
            'A.inputHeatSource',
            'A.inputRoofingShading',
            'A.inputWaterHeating',
            'A.xxinputPhone',
            'A.inputRoofType',
            'A.ElectricityProvider',
            'A.MonthlyPowerBill',
            'A.IncomingID',
            'A.universal_leadid',
            'A.utm_medium',
            'A.utm_term',
            'A.utm_content',
            'A.source_id',
            'A.AppointmentDateTime',
            'A.fullname',
            'A.VID',
            'A.TimeofAppointment',
            'A.utm_source',
            'A.utm_campaign',
            'A.AppointmentDate',
            'A.AppointmentTime',
            'A.revenue',
            'A.AID',
            'A.Cost',
        ];

        // pretty up the column names for headers
        $colnames = $columns;
        foreach ($colnames as &$name) {
            $name = substr($name, strpos($name, '.') + 1);
        }

        // create a temp file
        $file = tmpfile();
        fputcsv($file, $colnames);

        $totalrecs = 0;
        foreach ($this->getLeads($request, $columns) as $rec) {
            $totalrecs++;
            fputcsv($file, array_values($rec));
        }

        // Bail if no leads found
        if (!$totalrecs) {
            return;
        }

        // FTP the file
        $yesterday = Carbon::parse('yesterday', $request->tz)->format('Ymd');

        $targetfile = 'leads_' . $yesterday . '.csv';
        Storage::disk('ftp_' . $request->group_id)->put($targetfile, $file);

        fclose($file);

        // Send email with filename and total recs
        $message = [
            'to' => $email,
            'subject' => "Chase Data Lead Dump",
            'totalrecs' => $totalrecs,
            'targetfile' => $targetfile,
            'url' => url('/') . '/',
        ];

        try {
            Mail::to($message['to'])->send(new LeadDumpMail($message));
        } catch (\Exception $e) {
            // don't care
        }

        echo "$totalrecs pulled to $targetfile\n";
        echo "Emailed to $email\n";
    }

    /**
     * Get Leads
     * 
     * @param Request $request 
     * @param mixed $columns 
     * @return Generator<int, mixed>|Generator<int, array> 
     * @throws Exception 
     * @throws InvalidArgumentException 
     */
    public function getLeads(Request $request, $columns)
    {
        $this->db = $request->db;

        $from_date = Carbon::parse('yesterday', $request->tz)->tz('UTC')->toDateTimeString();
        $to_date = Carbon::parse('today', $request->tz)->tz('UTC')->toDateTimeString();

        $sql = "SELECT " . implode(',', $columns) . "
FROM [" . $this->db . "].[dbo].[Leads] L
LEFT JOIN [" . $this->db . "].[dbo].[ADVANCED_gridfields] A ON A.LeadId = L.IdGuid
WHERE L.GroupId = :group_id
AND Date >= :from_date
AND Date < :to_date";

        $bind = [
            'group_id' => $request->group_id,
            'from_date' => $from_date,
            'to_date' => $to_date,
        ];

        return $this->yieldSql($sql, $bind);
    }

    private function getAllInboundSources()
    {
        $sql = '';
        $union = '';
        foreach (Auth::user()->getDatabaseList() as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;

            $sql .= "$union SELECT DISTINCT Description
            FROM [$db].[dbo].[InboundSources]
            WHERE GroupId = :groupid$i
            AND InboundSource != ''";

            $union = ' UNION';
        }
        $sql .= " ORDER BY Description";

        return resultsToList($this->runSql($sql, $bind));
    }

    private function getAllCallStatuses()
    {
        $bind = [];

        $sql = '';
        $union = '';
        foreach (Auth::user()->getDatabaseList() as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;

            $sql .= "$union SELECT DISTINCT Disposition
            FROM [$db].[dbo].[Dispos]
            WHERE (GroupId = :groupid$i OR GroupId = -1)";

            $union = ' UNION';
        }
        $sql .= " ORDER BY Disposition";

        return resultsToList($this->runSql($sql, $bind));
    }
}
