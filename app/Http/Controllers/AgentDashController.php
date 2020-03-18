<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\DashTraits;

class AgentDashController extends Controller
{
    use DashTraits;

    /**
     * Display dashboard
     *
     * @param Request $request
     * @return view
     */
    public function index(Request $request)
    {
        $this->getSession($request);

        $campaigns = $this->campaignGroups();

        $jsfile[] = "agentdash.js";
        $cssfile[] = "agentdash.css";

        $data = [
            'isApi' => $this->isApi,
            'campaign' => $this->campaign,
            'dateFilter' => $this->dateFilter,
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
        $details = $this->filterDetails($this->dateFilter);

        $time_labels = [];
        $outbound = [];
        $inbound = [];
        $manual = [];

        $tot_outbound = 0;
        $tot_inbound = 0;
        $tot_manual = 0;
        $tot_total = 0;
        $duration = 0;

        foreach ($result as $r) {
            if ($this->byHour($this->dateFilter)) {
                $datetime = date("g:i", strtotime($r['Time']));
            } else {
                $datetime = date("D n/j/y", strtotime($r['Time']));
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
        $avg_handle_time = $this->secondsToHms($tot_total != 0 ? round($duration / $tot_total) : 0);

        return ['call_volume' => [
            'time' => $time_labels,
            'outbound' => $outbound,
            'inbound' => $inbound,
            'manual' => $manual,
            'tot_outbound' => $tot_outbound,
            'tot_inbound' => $tot_inbound,
            'tot_manual' => $tot_manual,
            'tot_total' => $tot_total,
            'avg_handle_time' => $avg_handle_time,
            'details' => $details,
        ]];
    }

    /**
     * Query call volume
     *
     * @param boolean $prev
     * @return void
     */
    private function getCallVolume()
    {
        $tz = Auth::user()->tz;

        $dateFilter = $this->dateFilter;
        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $fromDate = $fromDate->format('Y-m-d H:i:s');
        $toDate = $toDate->format('Y-m-d H:i:s');

        $byHour = $this->byHour($dateFilter);

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
            AND DR.CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound','Inbound Voicemail','TRANSFERRED','PARKED','SMS Received','SMS Delivered')
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
        return array_map(array(&$this, $mapFunction), $result);
    }

    public function campaignStats(Request $request)
    {
        $this->getSession($request);

        $result = $this->getCampaignStats();

        $total_talk_time = 0;

        // Compute averages
        foreach ($result as $campaign => &$rec) {
            $total_talk_time += $rec['TalkTime'];

            if ($rec['Calls'] == 0) {
                $rec['AvgTalkTime'] = 0;
                $rec['AvgHoldTime'] = 0;
                $rec['AvgHandleTime'] = 0;
                $rec['DropRate'] = 0;
            } else {
                $rec['AvgTalkTime'] = round($rec['TalkTime'] / $rec['Calls']);
                $rec['AvgHoldTime'] = round($rec['HoldTime'] / $rec['Calls']);
                $rec['AvgHandleTime'] = round(($rec['TalkTime'] + $rec['WrapUpTime']) / $rec['Calls']);
                $rec['DropRate'] = round($rec['Drops'] / $rec['Calls'] * 100, 2);
            }
        }

        // return separate arrays for each item
        return [
            'campaign_stats' => [
                'TotalTalkTime' => $this->secondsToHms($total_talk_time),
                'Campaign' => array_column($result, 'Campaign'),
                'AvgTalkTime' => array_column($result, 'AvgTalkTime'),
                'AvgHoldTime' => array_column($result, 'AvgHoldTime'),
                'AvgHandleTime' => array_column($result, 'AvgHandleTime'),
                'DropRate' => array_column($result, 'DropRate'),
            ]
        ];
    }

    private function getCampaignStats()
    {
        $activity = $this->getCampaignActivity();
        $dialingresults = $this->getCampaignDialingresults();

        // combine two results
        $final = [];

        foreach ($activity as $rec) {
            $final[$rec['Campaign']]['Campaign'] = $rec['Campaign'];
            $final[$rec['Campaign']]['Calls'] = $rec['Calls'];
            $final[$rec['Campaign']]['TalkTime'] = $rec['TalkTime'];
            $final[$rec['Campaign']]['WrapUpTime'] = $rec['WrapUpTime'];
            $final[$rec['Campaign']]['HoldTime'] = 0;
            $final[$rec['Campaign']]['Drops'] = 0;
        }

        foreach ($dialingresults as $rec) {
            if (!isset($final[$rec['Campaign']])) {
                $final[$rec['Campaign']]['Campaign'] = $rec['Campaign'];
                $final[$rec['Campaign']]['Calls'] = 0;
                $final[$rec['Campaign']]['TalkTime'] = 0;
                $final[$rec['Campaign']]['WrapUpTime'] = 0;
            }
            $final[$rec['Campaign']]['HoldTime'] = $rec['HoldTime'];
            $final[$rec['Campaign']]['Drops'] = $rec['Drops'];
        }

        return $final;
    }

    private function getCampaignActivity()
    {
        $tz = Auth::user()->tz;

        $dateFilter = $this->dateFilter;
        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $fromDate = $fromDate->format('Y-m-d H:i:s');
        $toDate = $toDate->format('Y-m-d H:i:s');

        $bind = [];

        $sql = "SELECT Campaign,
        'Calls' = SUM([Calls]),
        'TalkTime' = SUM([TalkTime]),
        'WrapUpTime' = SUM([WrapUpTime])
        FROM (";

        $union = '';
        foreach ($this->databases as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $fromDate;
            $bind['todate' . $i] = $toDate;
            $bind['rep' . $i] = $this->rep;

            $sql .= " $union SELECT AA.Campaign,
            'Calls' = SUM(CASE WHEN AA.Action IN ('Call', 'ManualCall', 'InboundCall') THEN 1 ELSE 0 END),
            'TalkTime' = SUM(CASE WHEN AA.Action IN ('Call', 'ManualCall', 'InboundCall') THEN AA.Duration ELSE 0 END),
            'WrapUpTime' = SUM(CASE WHEN AA.Action = 'Disposition' THEN AA.Duration ELSE 0 END)
            FROM [$db].[dbo].[AgentActivity] AA
            WHERE AA.GroupId = :groupid$i
            AND AA.Rep = :rep$i
            AND AA.Date >= :fromdate$i
            AND AA.Date < :todate$i
            GROUP BY AA.Campaign";

            $union = 'UNION ALL';
        }
        $sql .= ") tmp
        GROUP BY Campaign";

        return $this->runSql($sql, $bind);
    }

    private function getCampaignDialingresults()
    {
        $tz = Auth::user()->tz;

        $dateFilter = $this->dateFilter;
        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $fromDate = $fromDate->format('Y-m-d H:i:s');
        $toDate = $toDate->format('Y-m-d H:i:s');

        $bind = [];

        $sql = "SELECT Campaign,
        'HoldTime' = SUM([Holdtime]),
        'Drops' = SUM([Drops])
        FROM (";

        $union = '';
        foreach ($this->databases as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $fromDate;
            $bind['todate' . $i] = $toDate;
            $bind['rep' . $i] = $this->rep;

            $sql .= " $union SELECT DR.Campaign,
	        'Holdtime' = SUM(DR.HoldTime),
			'Drops' = SUM(CASE WHEN DR.CallStatus = 'CR_HANGUP' THEN 1 ELSE 0 END)
            FROM [$db].[dbo].[DialingResults] DR
            WHERE DR.Duration > 0
            AND DR.CallType NOT IN (7,8)
            AND DR.CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound','Inbound Voicemail','TRANSFERRED','PARKED','SMS Received','SMS Delivered')
            AND DR.GroupId = :groupid$i
            AND DR.Rep = :rep$i
            AND DR.Date >= :fromdate$i
			AND DR.Date < :todate$i
            GROUP BY Campaign";

            $union = 'UNION ALL';
        }
        $sql .= ") tmp
        GROUP BY Campaign";

        return $this->runSql($sql, $bind);
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

        $calls_time = 0;
        $paused_time = 0;
        $waiting_time = 0;
        $wrapup_time = 0;
        $total_time = 0;

        foreach ($result as $r) {
            if ($this->byHour($this->dateFilter)) {
                $datetime = date("g:i", strtotime($r['Time']));
            } else {
                $datetime = date("D n/j/y", strtotime($r['Time']));
            }

            array_push($calls_time_array, $r['Calls']);
            array_push($paused_time_array, $r['Paused']);
            array_push($waiting_time_array, $r['Waiting']);
            array_push($wrapup_time_array, $r['Wrap Up Time']);

            array_push($time_labels, $datetime);
            array_push($calls, $r['Calls']);
            array_push($paused, $r['Paused']);
            array_push($waiting, $r['Waiting']);
            array_push($wrapup, $r['Wrap Up Time']);

            $calls_time += $r['Calls'];
            $paused_time += $r['Paused'];
            $waiting_time += $r['Waiting'];
            $wrapup_time += $r['Wrap Up Time'];
        }

        $total_time = $calls_time + $paused_time + $waiting_time + $wrapup_time;

        $calls_time = $this->secondsToHms(round($calls_time));
        $paused_time = $this->secondsToHms(round($paused_time));
        $waiting_time = $this->secondsToHms(round($waiting_time));
        $wrapup_time = $this->secondsToHms(round($wrapup_time));
        $total_time = $this->secondsToHms(round($total_time));

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
        $tz = Auth::user()->tz;

        $dateFilter = $this->dateFilter;
        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $fromDate = $fromDate->format('Y-m-d H:i:s');
        $toDate = $toDate->format('Y-m-d H:i:s');

        // list($fromDate, $toDate) = $this->dateRange($dateFilter);

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

    public function callStatusCount(Request $request)
    {
        $this->getSession($request);

        $result = $this->getCallStatusCount();

        $labels = [];
        $data = [];

        foreach ($result as $r) {
            array_push($labels, $r['CallStatus']);
            array_push($data, $r['Call Count']);
        }

        return ['call_status_count' => [
            'labels' => $labels,
            'data' => $data,
        ]];
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
            WHERE DR.CallStatus NOT IN( 'CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound','Inbound Voicemail','TRANSFERRED','PARKED','SMS Delivered', 'SMS Received')
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

        return ['total_sales' => $result['Sales']];
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

            $sql .= " $union SELECT 'Sales' = COUNT(id)
                FROM [$db].[dbo].[DialingResults] DR
                CROSS APPLY (SELECT TOP 1 [Type]
                    FROM  [$db].[dbo].[Dispos] DI
                    WHERE Disposition = DR.CallStatus
                    AND (GroupId = DR.GroupId OR IsSystem=1)
                    AND (Campaign = DR.Campaign OR Campaign = '')
                    ORDER BY [id]) DI
                WHERE DR.GroupId = :groupid$i
                AND DR.Rep = :rep$i
                AND DR.Date >= :fromdate$i
                AND DR.Date < :todate$i
                AND DR.CallType IN (1,11)
                AND DI.Type = 3";

            $union = 'UNION ALL';
        }
        $sql .= ") tmp";

        $result = $this->runSql($sql, $bind);
        return $result[0];
    }
}
