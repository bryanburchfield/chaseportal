<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Campaign;
use \App\Traits\DashTraits;

class AgentDashController extends Controller
{
    private $rep;

    use DashTraits;

    /**
     * Display dashboard
     *
     * @param Request $request
     * @return view
     */
    public function index(Request $request)
    {
        $this->rep = $request->rep;

        $this->getSession($request);

        $campaigns = $this->campaignGroups();

        $jsfile[] = "agentdash.js";
        $cssfile[] = "agentdash.css";

        $data = [
            'isApi' => $this->isApi,
            'campaign' => $this->campaign,
            'datefilter' => $this->dateFilter,
            'inorout' => $this->inorout,
            'campaign_list' => $campaigns,
            'curdash' => 'agentdash',
            'jsfile' => $jsfile,
            'cssfile' => $cssfile,
        ];
        return view('agentdash')->with($data);
    }

    /**
     * return call volume
     *
     * @param Request $request
     * @return void
     */
    public function callVolume(Request $request)
    {
        $this->getSession($request);

        $result = $this->getCallVolume();

        $time_labels = [];
        $outbound = [];
        $inbound = [];
        $manual = [];
        $new_result = [];

        $tot_outbound = 0;
        $tot_inbound = 0;
        $tot_manual = 0;
        $tot_total = 0;
        $duration = 0;

        foreach ($result as $r) {
            if (!strpos($r['Time'], ':')) {
                $datetime = date("n/j/y", strtotime($r['Time']));
            } else {
                $datetime = $r['Time'];
            }

            $tot_outbound += $r['Outbound'];
            $tot_manual += $r['Manual'];
            $tot_inbound += $r['Inbound'];
            $duration += $r['Duration'];

            array_push($time_labels, $datetime);
            array_push($inbound, $r['Inbound']);
            array_push($outbound, $r['Outbound']);
            array_push($manual, $r['Manual']);
        }

        $tot_total = $tot_inbound + $tot_outbound + $tot_manual;

        if ($tot_total) {
            $avg_handle_time = secondsToHms($duration / $tot_total);
        } else {
            $avg_handle_time = '00:00:00';
        }
        $duration = date('H:i:s', $duration);

        $new_result['time'] = $time_labels;
        $new_result['outbound'] = $outbound;
        $new_result['inbound'] = $inbound;
        $new_result['manual'] = $manual;
        $new_result['tot_outbound'] = $tot_outbound;
        $new_result['tot_inbound'] = $tot_inbound;
        $new_result['tot_manual'] = $tot_manual;
        $new_result['tot_total'] = $tot_total;
        $new_result['avg_handle_time'] = $avg_handle_time;

        $details = $this->filterDetails($this->dateFilter);
        $new_result['details'] = $details;

        return ['call_volume' => $new_result];
    }

    /**
     * Query call volume
     *
     * @param boolean $prev
     * @return void
     */
    private function getCallVolume()
    {
        $tz = Auth::user()->getIanaTz();

        $dateFilter = $this->dateFilter;
        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $fromDate = $fromDate->format('Y-m-d H:i:s');
        $toDate = $toDate->format('Y-m-d H:i:s');

        $byHour = ($dateFilter == 'today' || $dateFilter == 'yesterday') ? true : false;

        // group by date/hour or just date
        if ($byHour) {
            $mapFunction = 'dateTimeToHour';
            $format = 'Y-m-d H:i:s.000';
            $modifier = "+1 hour";
            $xAxis = "DATEADD(HOUR, DATEPART(HOUR, CONVERT(datetimeoffset, DR.Date) AT TIME ZONE '$tz'),
            CAST(CAST(CONVERT(datetimeoffset, DR.Date) AT TIME ZONE '$tz' AS DATE) AS DATETIME))";
        } else {
            $mapFunction = 'dateTimeToDay';
            $format = 'Y-m-d 00:00:00.000';
            $modifier = "+1 day";
            $xAxis = "CAST(CAST(CONVERT(datetimeoffset, DR.Date) AT TIME ZONE '$tz' AS DATE) AS DATETIME)
            ";
        }

        $bind = [];

        $sql = "SELECT Time,
        'Outbound' = SUM([Outbound]),
        'Inbound' = SUM([Inbound]),
        'Manual' = SUM([Manual]),
        'Duration' = SUM([Duration])
        FROM (";

        $union = '';
        foreach ($this->databases as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $fromDate;
            $bind['todate' . $i] = $toDate;
            $bind['rep' . $i] = $this->rep;

            $sql .= " $union SELECT $xAxis as 'Time',
	        'Outbound' = SUM(CASE WHEN DR.CallType NOT IN (1,2,11) THEN 1 ELSE 0 END),
			'Inbound' = SUM(CASE WHEN DR.CallType IN (1,11) THEN 1 ELSE 0 END),
			'Manual' = SUM(CASE WHEN DR.CallType IN (2) THEN 1 ELSE 0 END),
            'Duration' = SUM(DR.Duration)
            FROM [$db].[dbo].[DialingResults] DR
            WHERE DR.Duration <> 0
            AND DR.CallType NOT IN (7,8)
            AND DR.CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound','TRANSFERRED','PARKED','SMS Received','SMS Delivered')
            AND DR.GroupId = :groupid$i
            AND DR.Rep = :rep$i
            AND DR.Date >= :fromdate$i
			AND DR.Date < :todate$i
            GROUP BY $xAxis";

            $union = 'UNION ALL';
        }
        $sql .= ") tmp
        GROUP BY [Time]
        ORDER BY [Time]";

        $result = $this->runSql($sql, $bind);

        $params = [
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'modifier' => $modifier,
            'byHour' => $byHour,
            'format' => $format,
            'zeroRec' => [
                'Time' => '',
                'Outbound' => 0,
                'Inbound' => 0,
                'Manual' => 0,
                'Duration' => 0,
            ],
        ];

        $result = $this->formatVolume($result, $params);

        // now format the xAxis datetimes and return the results
        return  array_map(array(&$this, $mapFunction), $result);
    }

    public function repPerformance(Request $request)
    {
        $this->getSession($request);

        $result = $this->getRepPerformance();

        $time_labels = [];
        $calls = [];
        $calls_time_array = [];
        $paused = [];
        $paused_time_array = [];
        $waiting = [];
        $waiting_time_array = [];
        $wrapup = [];
        $wrapup_time_array = [];
        $total_array = [];

        function add_time($times)
        {
            $minutes = 0;
            $seconds = 0;
            foreach ($times as $time) {
                list($hour, $minute, $second) = explode(':', $time);
                $seconds += $hour * 3600;
                $seconds += $minute * 60;
                $seconds += $second;
            }

            $hours = floor($seconds / 3600);
            $seconds -= $hours * 3600;
            $minutes = floor($seconds / 60);
            $seconds -= $minutes * 60;

            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }

        foreach ($result as $r) {
            if (!strpos($r['Time'], ':')) {
                $datetime = date("n/j/y", strtotime($r['Time']));
            } else {
                $datetime = $r['Time'];
            }

            array_push($calls_time_array, $r['Calls']);
            array_push($paused_time_array, $r['Paused']);
            array_push($waiting_time_array, $r['Waiting']);
            array_push($wrapup_time_array, $r['Wrap Up Time']);

            $r['Calls'] = convert_to_seconds($r['Calls']);
            $r['Paused'] = convert_to_seconds($r['Paused']);
            $r['Waiting'] = convert_to_seconds($r['Waiting']);
            $r['Wrap Up Time'] = convert_to_seconds($r['Wrap Up Time']);

            array_push($time_labels, $datetime);
            array_push($calls, $r['Calls']);
            array_push($paused, $r['Paused']);
            array_push($waiting, $r['Waiting']);
            array_push($wrapup, $r['Wrap Up Time']);
        }

        function convert_to_seconds($t)
        {
            $hours = substr($t, 0, 2);
            $mins = substr($t, 3, 2);
            $secs = substr($t, 6, 2);

            $h = $hours * 3660;
            $m = $mins * 60;
            $s = $secs;
            $time = $h + $m + $s;
            return round($time / 60);
        }

        $calls_time = add_time($calls_time_array);
        $paused_time = add_time($paused_time_array);
        $waiting_time = add_time($waiting_time_array);
        $wrapup_time = add_time($wrapup_time_array);

        $total_array = array_merge($calls_time_array, $paused_time_array, $waiting_time_array, $wrapup_time_array);
        $total_time = add_time($total_array);

        return [
            'rep_performance' => [
                'time' => $time_labels,
                'calls' => $calls,
                'paused' => $paused,
                'waiting' => $waiting,
                'wrapup' => $wrapup,
                'calls_time' => $calls_time,
                'paused_time' => $paused_time,
                'waiting_time' => $waiting_time,
                'wrapup_time' => $wrapup_time,
                'total' => $total_time
            ]
        ];
    }

    public function getRepPerformance()
    {
        $tz = Auth::user()->getIanaTz();

        $dateFilter = $this->dateFilter;
        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $fromDate = $fromDate->format('Y-m-d H:i:s');
        $toDate = $toDate->format('Y-m-d H:i:s');

        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        $byHour = ($dateFilter == 'today' || $dateFilter == 'yesterday') ? true : false;

        // group by date/hour or just date
        if ($byHour) {
            $mapFunction = 'dateTimeToHour';
            $format = 'Y-m-d H:i:s.000';
            $modifier = "+1 hour";
            $xAxis = "DATEADD(HOUR, DATEPART(HOUR, CONVERT(datetimeoffset, AA.Date) AT TIME ZONE '$tz'),
            CAST(CAST(CONVERT(datetimeoffset, AA.Date) AT TIME ZONE '$tz' AS DATE) AS DATETIME))";
        } else {
            $mapFunction = 'dateTimeToDay';
            $format = 'Y-m-d 00:00:00.000';
            $modifier = "+1 day";
            $xAxis = "CAST(CAST(CONVERT(datetimeoffset, AA.Date) AT TIME ZONE '$tz' AS DATE) AS DATETIME)";
        }

        $bind = [];

        $sql = "SELECT Time,
        'Calls' = CAST(DATEADD(SECOND, SUM([Calls]), 0) AS TIME(0)),
        'Paused' = CAST(DATEADD(SECOND, SUM([Paused]), 0) AS TIME(0)),
        'Waiting' = CAST(DATEADD(SECOND, SUM([Waiting]), 0) AS TIME(0)),
        'Wrap Up Time' = CAST(DATEADD(SECOND, SUM([Wrap Up Time]), 0) AS TIME(0))
        FROM (";

        $union = '';
        foreach ($this->databases as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $fromDate;
            $bind['todate' . $i] = $toDate;
            $bind['rep' . $i] = $this->rep;

            $sql .= " $union SELECT $xAxis Time,
            'Calls' = SUM(CASE WHEN AA.Action IN ('Call', 'ManualCall', 'InboundCall') THEN AA.Duration ELSE 0 END),
            'Paused' = SUM(CASE WHEN AA.Action = 'Paused' THEN AA.Duration ELSE 0 END),
            'Waiting' = SUM(CASE WHEN AA.Action = 'Waiting' THEN AA.Duration ELSE 0 END),
            'Wrap Up Time' = SUM(CASE WHEN AA.Action = 'Disposition' THEN AA.Duration ELSE 0 END)
            FROM [$db].[dbo].[AgentActivity] AA
            WHERE AA.GroupId = :groupid$i
            AND AA.Rep = :rep$i
            AND AA.Date >= :fromdate$i
            AND AA.Date < :todate$i
            GROUP BY $xAxis";

            $union = 'UNION ALL';
        }
        $sql .= ") tmp
        GROUP BY [Time]";

        $result = $this->runSql($sql, $bind);

        $params = [
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'modifier' => $modifier,
            'byHour' => $byHour,
            'format' => $format,
            'zeroRec' => [
                'Time' => '',
                'Calls' => '00:00:00',
                'Paused' => '00:00:00',
                'Waiting' => '00:00:00',
                'Wrap Up Time' => '00:00:00',
            ],
        ];

        $result = $this->formatVolume($result, $params);

        // now format the xAxis datetimes and return the results
        return  array_map(array(&$this, $mapFunction), $result);
    }

    public function callStatusCount(Request $request)
    {
        $this->getSession($request);

        $result = $this->getCallStatusCount();

        $labels = [];
        $data = [];
        $new_result = [];

        foreach ($result as $r) {
            array_push($labels, $r['CallStatus']);
            array_push($data, $r['Call Count']);
        }

        $new_result['labels'] = $labels;
        $new_result['data'] = $data;

        return ['call_status_count' =>  $new_result];
    }

    public function getCallStatusCount()
    {
        $dateFilter = $this->dateFilter;
        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $fromDate = $fromDate->format('Y-m-d H:i:s');
        $toDate = $toDate->format('Y-m-d H:i:s');

        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        $bind = [];

        $sql = "SELECT
         CallStatus,
         'Call Count' = SUM([Call Count])
         FROM (";

        $union = '';
        foreach ($this->databases as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $fromDate;
            $bind['todate' . $i] = $toDate;
            $bind['rep' . $i] = $this->rep;

            $sql .= " $union SELECT
                CallStatus,
                'Call Count' = COUNT(CallStatus)
            FROM [$db].[dbo].[DialingResults] DR
            WHERE DR.CallStatus NOT IN( 'CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound','TRANSFERRED','PARKED','SMS Delivered', 'SMS Received')
            AND DR.CallType NOT IN (7,8)
            AND DR.GroupId = :groupid$i
            AND DR.Rep = :rep$i
            AND DR.Date >= :fromdate$i
            AND DR.Date < :todate$i
            GROUP BY DR.CallStatus";

            $union = 'UNION ALL';
        }
        $sql .= ") tmp
        GROUP BY CallStatus";

        return $this->runSql($sql, $bind);
    }

    public function sales(Request $request)
    {
        $this->getSession($request);

        $result = $this->getSales();

        return ['total_sales' => $result];
    }

    public function getSales()
    {
        $dateFilter = $this->dateFilter;
        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $fromDate = $fromDate->format('Y-m-d H:i:s');
        $toDate = $toDate->format('Y-m-d H:i:s');

        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        $bind = [];

        $sql = "SELECT SUM(Sales) as Sales
        FROM (";

        $union = '';
        foreach ($this->databases as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $fromDate;
            $bind['todate' . $i] = $toDate;
            $bind['rep' . $i] = $this->rep;

            $sql .= " $union SELECT 'Sales' = COUNT(CASE WHEN DI.Type = '3' THEN 1 ELSE 0 END)
                FROM [$db].[dbo].[DialingResults] DR
                CROSS APPLY (SELECT TOP 1 [Type]
                    FROM  [$db].[dbo].[Dispos] DI
                    WHERE Disposition = DR.CallStatus
                    AND (GroupId = DR.GroupId OR IsSystem=1)
                    AND (Campaign = DR.Campaign OR Campaign = '')
                    ORDER BY [Description] Desc) DI
                WHERE DR.GroupId = :groupid$i
                AND DR.Rep = :rep$i
                AND DR.Date >= :fromdate$i
                AND DR.Date < :todate$i
                AND DR.CallType IN (1,11)
                AND DI.Type = '3'";

            $union = 'UNION ALL';
        }
        $sql .= ") tmp";

        return $this->runSql($sql, $bind);
    }
}
