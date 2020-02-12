<?php

namespace App\Http\Controllers;

use App\Traits\DashTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AdminDurationDashController extends Controller
{
    use DashTraits;

    public function index(Request $request)
    {
        $this->getSession($request);

        $campaigns = $this->campaignGroups();

        $jsfile[] = "admindurationdash.js";
        $cssfile[] = "admindurationdash.css";

        $data = [
            'isApi' => $this->isApi,
            'campaign' => $this->campaign,
            'dateFilter' => $this->dateFilter,
            'inorout' => $this->inorout,
            'campaign_list' => $campaigns,
            'curdash' => 'admindurationdash',
            'jsfile' => $jsfile,
            'cssfile' => $cssfile,
        ];

        return view('admindurationdash')->with($data);
    }

    public function callVolume(Request $request)
    {
        $this->getSession($request);

        $tz = Auth::user()->ianaTz;

        $details = $this->filterDetails();

        $results = $this->getCallVolume();

        // Initialize return vars
        $campaigns = [];
        $callstatuses = [];
        $dates = [];
        $total_calls = 0;
        $total_minutes = 0;

        // Loop thru results, buuld return vals
        foreach ($results as $rec) {
            if (!isset($campaigns[$rec['Campaign']])) {
                $campaigns[$rec['Campaign']]['Minutes'] = 0;
                $campaigns[$rec['Campaign']]['Count'] = 0;
            }
            $campaigns[$rec['Campaign']]['Minutes'] += $rec['secs'];
            $campaigns[$rec['Campaign']]['Count'] += $rec['cnt'];

            if (!isset($callstatuses[$rec['CallStatus']])) {
                $callstatuses[$rec['CallStatus']]['Minutes'] = 0;
                $callstatuses[$rec['CallStatus']]['Count'] = 0;
            }
            $callstatuses[$rec['CallStatus']]['Minutes'] += $rec['secs'];
            $callstatuses[$rec['CallStatus']]['Count'] += $rec['cnt'];

            $date = Carbon::parse($rec['Date'])
                ->tz($tz)
                ->isoFormat('MMM DD');

            if (!isset($dates[$date])) {
                $dates[$date]['Minutes'] = 0;
                $dates[$date]['Count'] = 0;
            }
            $dates[$date]['Minutes'] += $rec['secs'];
            $dates[$date]['Count'] += $rec['cnt'];
        }

        // Sort
        ksort($campaigns);
        ksort($dates);

        uasort($callstatuses, function ($a, $b) {
            return $b['Minutes'] <=> $a['Minutes'];
        });

        // Convert secs to mins
        unset($rec);
        foreach ($campaigns as &$rec) {
            $rec['Minutes'] = number_format($rec['Minutes'] / 60, 2);
        }
        unset($rec);
        foreach ($callstatuses as &$rec) {
            $rec['Minutes'] = number_format($rec['Minutes'] / 60, 2);
        }
        unset($rec);
        foreach ($dates as &$rec) {
            $rec['Minutes'] = number_format($rec['Minutes'] / 60, 2);
        }

        return ['call_volume' => [
            'campaigns' => $campaigns,
            'callstatuses' => $callstatuses,
            'dates' => $dates,
            'total_calls' => $total_calls,
            'total_minutes' => $total_minutes,
            'details' => $details,
        ]];
    }

    private function getCallVolume()
    {

        // and CallStatus like 'CR[_]%'   almost but not quite

        $campaign = $this->campaign;
        $dateFilter = $this->dateFilter;

        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $sql = "SELECT Date, Campaign, CallStatus, COUNT(*) cnt, SUM(Duration) secs
            FROM DialingResults DR
            WHERE GroupId = :groupid
            AND Date >= :fromdate
            AND Date < :todate
            AND CallType NOT IN (7,8)
            AND CallStatus NOT IN ('Inbound', 'CR_CNCT/CON_CAD', 'CR_CNCT/CON_PVD')
            GROUP BY Date, Campaign, CallStatus";

        $bind = [
            'groupid' => Auth::user()->group_id,
            'fromdate' => $startDate,
            'todate' => $endDate,
        ];

        list($where, $extrabind) = $this->campaignClause('DR', 0, $campaign);
        $sql .= " $where";
        $bind = array_merge($bind, $extrabind);

        return $this->runSql($sql, $bind);
    }
}
