<?php

namespace App\Http\Controllers;

use App\Traits\SqlServerTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeadsController extends Controller
{
    protected $db;

    use SqlServerTraits;

    public function rules(Request $request)
    {
        $page = [
            'menuitem' => 'tools',
            'type' => 'other',
        ];

        $data = [
            'user' => Auth::user(),
            'page' => $page
        ];

        return view('dashboards.tools')->with($data);
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
        foreach ($this->getLeads($request) as $i => $rec) {
            echo $i . " " . $rec['id'] . "\n";
        }
    }

    public function getLeads(Request $request)
    {
        $this->db = $request->db;

        $to_date = localToUtc(utcToLocal(new \DateTime, $request->tz)->format('Y-m-d'), $request->tz);
        $from_date = (clone $to_date)->modify('-1 day');

        $sql = "SELECT
L.id,
L.ClientId,
L.FirstName,
L.LastName,
L.Address,
L.City,
L.State,
L.ZipCode,
L.PrimaryPhone,
L.SecondaryPhone,
L.Rep,
L.CallStatus,
L.Date,
L.Campaign,
L.Attempt,
L.WasDialed,
L.LastUpdated,
L.Notes,
L.Subcampaign,
L.CallType,
L.FullName,
A.LID,
A.inputName,
A.inputEmail,
A.inputBill,
A.inputResidenceType,
A.inputOwnershiptype,
A.inputSquareFootage,
A.inputHeatSource,
A.inputRoofingShading,
A.inputWaterHeating,
A.xxinputPhone,
A.inputRoofType,
A.ElectricityProvider,
A.MonthlyPowerBill,
A.IncomingID,
A.universal_leadid,
A.utm_medium,
A.utm_term,
A.utm_content,
A.source_id,
A.AppointmentDateTime,
A.fullname,
A.VID,
A.TimeofAppointment,
A.utm_source,
A.utm_campaign,
A.AppointmentDate,
A.AppointmentTime,
A.revenue,
A.AID,
A.Cost
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
