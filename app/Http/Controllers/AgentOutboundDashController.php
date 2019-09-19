<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\DashTraits;

class AgentOutboundDashController extends Controller
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

        $jsfile[] = "agentoutbounddash.js";
        $cssfile[] = "agentoutbounddash.css";

        $data = [
            'isApi' => $this->isApi,
            'campaign' => $this->campaign,
            'datefilter' => $this->dateFilter,
            'inorout' => $this->inorout,
            'campaign_list' => $campaigns,
            'curdash' => 'agentoutbounddash',
            'jsfile' => $jsfile,
            'cssfile' => $cssfile,
        ];
        return view('agentoutbounddash')->with($data);
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

    private function getCallStatusCount()
    {
        $dateFilter = $this->dateFilter;
        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $fromDate = $fromDate->format('Y-m-d H:i:s');
        $toDate = $toDate->format('Y-m-d H:i:s');

        $bind = [];

        $sql = "SELECT CallStatus, 'Call Count' = SUM([Call Count])
                FROM (";

        $union = '';
        foreach ($this->databases as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $fromDate;
            $bind['todate' . $i] = $toDate;
            $bind['rep' . $i] = $this->rep;

            $sql .= " $union SELECT CallStatus, 'Call Count' = COUNT(CallStatus)
            FROM [$db].[dbo].[DialingResults] DR
            WHERE DR.CallStatus NOT IN( 'CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound','TRANSFERRED','PARKED','SMS Delivered', 'SMS Received')
            AND DR.CallType NOT IN ('7','8')
            AND DR.GroupId = :groupid$i
            AND DR.Rep = :rep$i
            AND DR.Date >= :fromdate$i
            AND DR.Date < :todate$i
            GROUP BY(DR.CallStatus)";

            $union = 'UNION ALL';
        }
        $sql .= ") tmp
        GROUP BY(CallStatus)";

        return $this->runSql($sql, $bind);
    }

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

        if ($tot_inbound) {
            $avg_handle_time = secondsToHms($duration / $tot_inbound);
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

        $sql = "SELECT [Time],
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

            $sql .= " $union SELECT $xAxis Time,
	        'Outbound' = SUM(CASE WHEN DR.CallType NOT IN (1,2,11) THEN 1 ELSE 0 END),
			'Inbound' = SUM(CASE WHEN DR.CallType IN (1,11) THEN 1 ELSE 0 END),
			'Manual' = SUM(CASE WHEN DR.CallType IN (2) THEN 1 ELSE 0 END),
            'Duration' = SUM(CASE WHEN DR.CallType IN (1,11) THEN DR.Duration ELSE 0 END)
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

    public function sales(Request $request)
    {
        $this->getSession($request);

        $result = $this->getSales();

        return ['total_sales' => $result];
    }

    private function getSales()
    {
        $dateFilter = $this->dateFilter;
        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $fromDate = $fromDate->format('Y-m-d H:i:s');
        $toDate = $toDate->format('Y-m-d H:i:s');

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
                AND DR.CallType NOT IN (1,11)
                AND DI.Type = '3'";

            $union = 'UNION ALL';
        }
        $sql .= ") tmp";

        $result = $this->runSql($sql, $bind);

        // Determine manhours
        $bind = [];

        $sql = "SELECT SUM(Duration) as Duration
        FROM (";

        $union = '';
        foreach ($this->databases as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $fromDate;
            $bind['todate' . $i] = $toDate;
            $bind['rep' . $i] = $this->rep;

            $sql .= " $union SELECT  SUM(Duration) as Duration
            FROM [$db].[dbo].[AgentActivity] WITH(NOLOCK)
            WHERE GroupId = :groupid$i
            AND Rep = :rep$i
            AND Date >= :fromdate$i
            AND Date < :todate$i
            AND [Action] NOT IN ('Login','Logout','Paused')";

            $union = 'UNION ALL';
        }
        $sql .= ") tmp";

        $manhours = $this->runSql($sql, $bind);

        $manhours = empty($manhours[0]['Duration']) ? 0 : $manhours[0]['Duration'];

        $result['ManHours'] = $manhours[0] / 60 / 60;

        $result['SalesPerHour'] = empty($result['ManHours']) ? 0 : round($result['Sales'] / $result['ManHours'], 2);

        return $result;
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

        $calls_time = 0;
        $paused_time = 0;
        $waiting_time = 0;
        $wrapup_time = 0;

        foreach ($result as $r) {
            if (!strpos($r['Time'], ':')) {
                $datetime = date("n/j/y", strtotime($r['Time']));
            } else {
                $datetime = $r['Time'];
            }

            $calls_time += $r['Calls'];
            $paused_time += $r['Paused'];
            $waiting_time += $r['Waiting'];
            $wrapup_time += $r['Wrap Up Time'];

            array_push($calls_time_array, $r['Calls']);
            array_push($paused_time_array, $r['Paused']);
            array_push($waiting_time_array, $r['Waiting']);
            array_push($wrapup_time_array, $r['Wrap Up Time']);

            array_push($time_labels, $datetime);
            array_push($calls, $r['Calls']);
            array_push($paused, $r['Paused']);
            array_push($waiting, $r['Waiting']);
            array_push($wrapup, $r['Wrap Up Time']);
        }

        $total_array = array_merge($calls_time_array, $paused_time_array, $waiting_time_array, $wrapup_time_array);
        $total_time = $calls_time + $paused_time + $waiting_time + $wrapup_time;

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

    private function getRepPerformance()
    {
        $tz = Auth::user()->tz;

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
        'Calls' = SUM([Calls]),
        'Paused' = SUM([Paused]),
        'Waiting' = SUM([Waiting]),
        'Wrap Up Time' = SUM([Wrap Up Time])
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
                'Calls' => '0',
                'Paused' => '0',
                'Waiting' => '0',
                'Wrap Up Time' => '0',
            ],
        ];

        $result = $this->formatVolume($result, $params);

        // now format the xAxis datetimes and return the results
        return  array_map(array(&$this, $mapFunction), $result);
    }
}
