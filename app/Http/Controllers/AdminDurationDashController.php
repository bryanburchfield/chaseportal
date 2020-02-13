<?php

namespace App\Http\Controllers;

use App\Traits\DashTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AdminDurationDashController extends Controller
{
    use DashTraits;

    // https://powerbi.chasedatacorp.com/PowerBiEmbedded/Home/EmbedReport?workspaceid=5072828d-6001-4717-91e2-d154cb48159d&reportid=991adf03-e2a7-4167-9bb9-3ffffb6a240e

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
        $total_seconds = 0;
        $connect_pct = 0;
        $system_pct = 0;


        // Loop thru results, buuld return vals
        foreach ($results as $rec) {
            $total_calls += $rec['cnt'];
            $total_seconds += $rec['secs'];

            // We'll convert this to pct later
            if (substr($rec['CallStatus'], 0, 3) == 'CR_') {
                $system_pct += $rec['cnt'];
            }

            if (!isset($campaigns[$rec['Campaign']])) {
                $campaigns[$rec['Campaign']]['Camnpaign'] = $rec['Campaign'];
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
                $dates[$date]['Seconds'] = 0;
                $dates[$date]['Count'] = 0;
            }
            $dates[$date]['Seconds'] += $rec['secs'];
            $dates[$date]['Count'] += $rec['cnt'];
        }

        // Calculate percents
        $connect_pct = $total_calls - $system_pct;
        $connect_pct = round($connect_pct / $total_calls * 100, 2) . '%';
        $system_pct = round($system_pct / $total_calls * 100, 2) . '%';

        // Sort
        ksort($campaigns);
        ksort($dates);

        uasort($callstatuses, function ($a, $b) {
            return $b['Minutes'] <=> $a['Minutes'];
        });

        // Convert secs to mins
        foreach ($campaigns as &$rec) {
            $rec['Minutes'] = round($rec['Minutes'] / 60, 2);
        }
        foreach ($callstatuses as &$rec) {
            $rec['Minutes'] = round($rec['Minutes'] / 60, 0);
        }

        return ['call_volume' => [
            'campaigns' => $campaigns,
            'callstatuses' => $callstatuses,
            'dates' => $dates,
            'total_calls' => $total_calls,
            'total_seconds' => $total_seconds,
            'connect_pct' => $connect_pct,
            'system_pct' => $system_pct,
            'details' => $details,
        ]];
    }

    private function getCallVolume()
    {
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
