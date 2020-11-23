<?php

namespace App\Http\Controllers;

use App\Includes\PowerImportAPI;
use App\Mail\LeadDumpMail;
use App\Models\Dialer;
use App\Models\Lead;
use App\Traits\CampaignTraits;
use App\Traits\SqlServerTraits;
use App\Traits\TimeTraits;
use Exception;
use Illuminate\Http\Request;
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
    public $currentDash;

    public function leadDetail(Lead $lead = null)
    {
        $errors = [];

        if ($lead) {
            if ($lead->GroupId != Auth::user()->group_id) {
                $lead = null;
                $errors['id'] = trans('tools.lead_not_found');
            }
        }

        $this->currentDash = session('currentDash', 'inbounddash');
        session(['currentDash' => $this->currentDash]);

        $jsfile[] = '';
        $page['menuitem'] = 'lead_detail';
        $page['sidenav'] = 'main';
        $page['type'] = 'page';
        $data = [
            'page' => $page,
            'lead' => $lead,
            'errors' => $errors,
        ];

        return view('tools.lead_detail')->with($data);
    }

    private function pickLead($leads)
    {
        $this->currentDash = session('currentDash', 'inbounddash');
        session(['currentDash' => $this->currentDash]);

        $jsfile[] = '';
        $page['menuitem'] = 'lead_detail';
        $page['sidenav'] = 'main';
        $page['type'] = 'page';
        $data = [
            'page' => $page,
            'leads' => $leads,
        ];

        return view('tools.pick_lead')->with($data);
    }

    public function getLead(Request $request)
    {
        $lead = null;

        if (!empty($request->id)) {
            if ($request->search_key == 'phone') {
                try {
                    $lead = Lead::where('PrimaryPhone', $this->formatPhone($request->id))
                        ->where('GroupId', Auth::user()->group_id)
                        ->get();
                } catch (Exception $e) {
                    $lead = null;
                }
            } else {
                try {
                    $lead = Lead::where('id', $request->id)->get();
                } catch (Exception $e) {
                    $lead = null;
                }
            }

            if ($lead) {
                if ($lead->count() > 1) {
                    return $this->pickLead($lead);
                }

                $lead = $lead->first();
            }

            if (!$lead) {
                session()->flash('flasherror', trans('tools.lead_not_found'));
            }
        }

        return redirect()->action(
            'LeadsController@leadDetail',
            ['lead' => $lead]
        );
    }

    public function updateLead(Lead $lead, Request $request)
    {
        // These two should never happen
        if (!$lead) {
            session()->flash('flasherror', trans('tools.lead_not_found'));
            return redirect()->back()->withInput();
        }

        if ($lead->GroupId != Auth::user()->group_id) {
            session()->flash('flasherror', trans('tools.lead_not_found'));
            return redirect()->back()->withInput();
        }

        $fqdn = Dialer::where('id', Auth::user()->dialer_id)->pluck('dialer_fqdn')->first();
        $api = new PowerImportAPI('http://' . $fqdn . '/PowerStudio/WebAPI');

        // validation?

        // update lead table first
        $data = $this->getLeadUpdateFields($lead, $request);
        if (!empty($data)) {
            $result = $api->UpdateDataByLeadId($data, Auth::user()->group_id, '', '', $lead->id);
        }

        // update custom table next
        $data = $this->getCustomUpdateFields($lead, $request);
        if (!empty($data)) {
            $result = $api->UpdateDataByLeadId($data, Auth::user()->group_id, '', '', $lead->id);
        }

        $lead->refresh();  // probably not updated yet, but wth

        session()->flash('flashsuccess', trans('tools.lead_updated'));

        return redirect()->action('LeadsController@leadDetail');
    }

    private function getLeadUpdateFields(Lead $lead, Request $request)
    {
        $data = [];

        foreach ((new Lead)->getFillable() as $field) {
            if ($request->exists($field)) {
                if ($this->valueChange($lead->$field, $request->input($field))) {
                    $data[$field] = $request->input($field);
                }
            }
        }

        return $data;
    }

    private function getCustomUpdateFields(Lead $lead, Request $request)
    {
        $data = [];

        foreach ($lead->customFields() as $rec) {
            if ($request->exists($rec['key'])) {
                if ($this->valueChange($rec['value'], $request->input($rec['key']))) {
                    $data[$rec['key']] = $request->input($rec['key']);
                }
            }
        }

        return $data;
    }

    private function valueChange($old, $new)
    {
        if (
            (empty($old) && empty($new)) ||
            ($new === $old)
        ) {
            return false;
        }

        return true;
    }

    private function formatPhone($phone)
    {
        return preg_replace('/[^0-9]/', '', $phone);
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
}
