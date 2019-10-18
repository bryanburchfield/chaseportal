<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddLeadFilterRule;
use App\LeadRule;
use App\Mail\LeadDumpMail;
use App\Traits\SqlServerTraits;
use App\Traits\CampaignTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class LeadsController extends Controller
{
    use SqlServerTraits;
    use CampaignTraits;

    protected $db;

    public function rules()
    {
        $lead_rules = LeadRule::where('group_id', Auth::user()->group_id)
            ->OrderBy('rule_name')
            ->get();

        $campaigns = $this->getAllCampaigns();

        $page = [
            'menuitem' => 'tools',
            'type' => 'other',
        ];

        $data = [
            'user' => Auth::user(),
            'page' => $page,
            'group_id' => Auth::user()->group_id,
            'lead_rules' => $lead_rules,
            'campaigns' => $campaigns,
        ];

        return view('dashboards.tools')->with($data);
    }

    private function getRule($id)
    {
        return LeadRule::where('id', $id)
            ->where('group_id', Auth::user()->group_id)
            ->firstOrFail();
    }

    public function editLeadRule(Request $request)
    {
        $lr = $this->getRule($request->id);

        $campaigns = $this->getAllCampaigns();

        $subcampaigns = $this->getAllSubcampaigns($lr['source_campaign']);
        $destination_subcampaigns = $this->getAllSubcampaigns($lr['destination_campaign']);

        $page = [
            'menuitem' => 'tools',
            'type' => 'other',
        ];
        $data = [
            'lead_rule' => $lr,
            'page' => $page,
            'campaigns' => $campaigns,
            'subcampaigns' => $subcampaigns,
            'destination_subcampaigns' => $destination_subcampaigns
        ];

        return view('dashboards.tools_edit_rule')->with($data);
    }

    public function createRule(AddLeadFilterRule $request)
    {
        $lr = new LeadRule();
        $lr->fill($request->all());
        $lr->group_id = Auth::user()->group_id;
        $lr->active = true;
        $lr->save();

        return $this->rules();
    }

    public function updateRule(AddLeadFilterRule $request)
    {
        // We don't actually update a rule, we'll (soft) delete
        // and insert a new one
        $lr = $this->getRule($request->id);

        $lr->fill($request->all());

        if ($lr->isDirty()) {
            $lr->delete();
            return $this->createRule($request);
        }

        return $this->rules();
    }

    public function deleteRule(Request $request)
    {
        $lr = $this->getRule($request->id);

        return $lr->delete();
    }

    public function getCampaigns(Request $request)
    {
        $fromDate = $request->fromdate;
        $toDate = $request->todate;

        $results = $this->getAllCampaigns($fromDate, $toDate);

        return ['campaigns' => array_values($results)];
    }

    public function getSubcampaigns(Request $request)
    {
        $campaign = $request->campaign;

        $results = $this->getAllSubcampaigns($campaign);

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

        // If there are recs, FTP the file
        if ($totalrecs) {
            $yesterday = (localToUtc(utcToLocal(new \DateTime, $request->tz)->format('Y-m-d'), $request->tz))->modify('-1 day');
            $yesterday = utcToLocal($yesterday, $request->tz)->format('Ymd');

            $targetfile = 'leads_' . $yesterday . '.csv';
            Storage::disk('ftp_' . $request->group_id)->put($targetfile, $file);
        }
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

    public function getLeads(Request $request, $columns)
    {
        $this->db = $request->db;

        $to_date = localToUtc(utcToLocal(new \DateTime, $request->tz)->format('Y-m-d'), $request->tz);
        $from_date = (clone $to_date)->modify('-1 day');

        $sql = "SELECT " . implode(',', $columns) . "
FROM [PowerV2_Reporting_Dialer-19].[dbo].[Leads] L
LEFT JOIN [PowerV2_Reporting_Dialer-19].[dbo].[ADVANCED_gridfields] A ON A.LeadId = L.IdGuid
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
}
