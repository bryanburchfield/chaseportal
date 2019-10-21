<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\DashTraits;

class TrendDashController extends Controller
{
    use DashTraits;

    public function index(Request $request)
    {
        $this->getSession($request);

        $campaigns = $this->campaignGroups();

        $jsfile[] = "trenddash.js";
        $cssfile[] = "trenddash.css";

        $data = [
            'isApi' => $this->isApi,
            'campaign' => $this->campaign,
            'datefilter' => $this->dateFilter,
            'inorout' => $this->inorout,
            'campaign_list' => $campaigns,
            'curdash' => 'trenddash',
            'jsfile' => $jsfile,
            'cssfile' => $cssfile,
        ];
        return view('trenddash')->with($data);
    }

    public function callVolume(Request $request)
    {
        $this->getSession($request);

        $result = $this->getCallVolume();
        $details = $this->filterDetails();

        $inbound_time_labels = [];
        $total_inbound_calls = [];
        $inbound_voicemails = [];
        $inbound_abandoned = [];
        $inbound_handled = [];

        $outbound_time_labels = [];
        $total_outbound_calls = [];
        $outbound_handled = [];
        $outbound_dropped = [];
        $total_calls = 0;

        foreach ($result[0] as $r) {
            if ($this->byHour($this->dateFilter)) {
                $datetime = date("g:i", strtotime($r['Time']));
            } else {
                $datetime = date("D n/j/y", strtotime($r['Time']));
            }

            array_push($inbound_time_labels, $datetime);
            array_push($total_inbound_calls, $r['Inbound Count']);
            array_push($inbound_voicemails, $r['Inbound Voicemails']);
            array_push($inbound_abandoned, $r['Inbound Abandoned Calls']);
            array_push($inbound_handled, $r['Inbound Handled Calls']);

            $total_calls += $r['Inbound Count'];
        }

        foreach ($result[1] as $r) {
            if ($this->byHour($this->dateFilter)) {
                $datetime = date("g:i", strtotime($r['Time']));
            } else {
                $datetime = date("D n/j/y", strtotime($r['Time']));
            }

            array_push($outbound_time_labels, $datetime);
            array_push($total_outbound_calls, $r['Outbound Count']);
            array_push($outbound_handled, $r['Outbound Handled Calls']);
            array_push($outbound_dropped, $r['Outbound Dropped Calls']);

            $total_calls += $r['Outbound Count'];
        }

        return ['call_volume' => [
            'inbound_time_labels' => $inbound_time_labels,
            'outbound_time_labels' => $outbound_time_labels,
            'total_inbound_calls' => $total_inbound_calls,
            'inbound_voicemails' => $inbound_voicemails,
            'inbound_abandoned' => $inbound_abandoned,
            'inbound_handled' => $inbound_handled,
            'outbound_handled' => $outbound_handled,
            'total_outbound_calls' => $total_outbound_calls,
            'outbound_dropped' => $outbound_dropped,
            'total' => $total_calls,
            'details' => $details,
        ]];
    }

    public function getCallVolume($prev = false)
    {
        $campaign = $this->campaign;
        $dateFilter = $this->dateFilter;
        $timeZoneName = Auth::user()->tz;

        if ($prev) {
            list($fromDate, $toDate) = $this->previousDateRange($dateFilter);
        } else {
            list($fromDate, $toDate) = $this->dateRange($dateFilter);
        }

        $byHour = $this->byHour($dateFilter);

        // group by date/hour or just date
        if ($byHour) {
            $mapFunction = 'dateTimeToHour';
            $format = 'Y-m-d H:i:s.000';
            $modifier = "+1 hour";
            $xAxis = "DATEADD(HOUR, DATEPART(HOUR, CONVERT(datetimeoffset, DR.Date) AT TIME ZONE '$timeZoneName'),
            CAST(CAST(CONVERT(datetimeoffset, DR.Date) AT TIME ZONE '$timeZoneName' AS DATE) AS DATETIME))";
        } else {
            $mapFunction = 'dateTimeToDay';
            $format = 'Y-m-d 00:00:00.000';
            $modifier = "+1 day";
            $xAxis = "CAST(CAST(CONVERT(datetimeoffset, DR.Date) AT TIME ZONE '$timeZoneName' AS DATE) AS DATETIME)
            ";
        }

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $bind = [];

        $sql = "SELECT
        Time,
        SUM([Inbound Count]) AS 'Inbound Count',
        SUM([Inbound Handled Calls]) AS 'Inbound Handled Calls',
        SUM([Inbound Voicemails]) AS 'Inbound Voicemails',
        SUM([Inbound Abandoned Calls]) AS 'Inbound Abandoned Calls',
        SUM([Outbound Count]) AS 'Outbound Count',
        SUM([Outbound Handled Calls]) AS 'Outbound Handled Calls',
        SUM([Outbound Dropped Calls]) AS 'Outbound Dropped Calls'
        FROM (";

        $union = '';
        foreach ($this->databases as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $startDate;
            $bind['todate' . $i] = $endDate;

            $sql .= " $union SELECT $xAxis as 'Time',
            'Inbound Count' = CASE WHEN DR.CallType IN (1,11) THEN 1 ELSE 0 END,
            'Inbound Handled Calls' = CASE WHEN DR.CallType IN (1,11) AND DR.CallStatus NOT IN ( 'CR_CEPT', 'CR_CNCT/CON_PAMD', 'CR_NOANS', 'CR_NORB', 'CR_BUSY',
            'CR_DROPPED', 'CR_FAXTONE', 'CR_FAILED', 'CR_DISCONNECTED',
            'CR_HANGUP', 'Inbound Voicemail') THEN 1 ELSE 0 END,
            'Inbound Voicemails' = CASE WHEN DR.CallType IN (1,11) AND DR.CallStatus='Inbound Voicemail' THEN 1 ELSE 0 END,
            'Inbound Abandoned Calls' = CASE WHEN DR.CallType IN (1,11) AND DR.CallStatus='CR_HANGUP' THEN 1 ELSE 0 END,
            'Outbound Count' = CASE WHEN DR.CallType NOT IN (1,11) THEN 1 ELSE 0 END,
            'Outbound Handled Calls' = CASE WHEN DR.CallType NOT IN (1,11) AND DR.CallStatus NOT IN ('CR_CEPT', 'CR_CNCT/CON_PAMD', 'CR_NOANS', 'CR_NORB', 'CR_BUSY',
            'CR_DROPPED', 'CR_FAXTONE', 'CR_FAILED', 'CR_DISCONNECTED',
            'CR_HANGUP', 'Inbound Voicemail') THEN 1 ELSE 0 END,
            'Outbound Dropped Calls' = CASE WHEN DR.CallType NOT IN (1,11) AND DR.CallStatus='CR_DROPPED' THEN 1 ELSE 0 END
            FROM [$db].[dbo].[DialingResults] DR
            WHERE DR.CallType NOT IN (7,8)
            AND DR.CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound')
            AND Duration > 0
            AND DR.Date >= :fromdate$i
            AND DR.Date < :todate$i
            AND DR.GroupId = :groupid$i";

            list($where, $extrabind) = $this->campaignClause('DR', $i, $campaign);
            $sql .= " $where";
            $bind = array_merge($bind, $extrabind);

            $union = 'UNION ALL';
        }
        $sql .= ") tmp
        GROUP BY [Time]
        ORDER BY [Time]";

        $result = $this->runSql($sql, $bind);

        // split the results into three arrays
        $params = [
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'modifier' => $modifier,
            'byHour' => $byHour,
            'format' => $format,
        ];

        $inResult = $this->inboundVolume($result, $params);
        $outResult = $this->outboundVolume($result, $params);

        // now format the xAxis datetimes and return the results
        return [
            array_map(array(&$this, $mapFunction), $inResult),
            array_map(array(&$this, $mapFunction), $outResult),
        ];
    }

    private function inboundVolume($result, $params)
    {
        // extract Time and Inbound fields from array
        $inbound = [];
        foreach ($result as $rec) {
            foreach ($rec as $k => $v) {
                if ($k[0] !== 'I' && $k[0] !== 'T') {
                    unset($rec[$k]);
                }
            }
            $inbound[] = $rec;
        }

        // define recs with no data to compare against or insert if we need to fill in gaps
        $zeroRec = [
            'Time' => '',
            'Inbound Count' => 0,
            'Inbound Handled Calls' => 0,
            'Inbound Voicemails' => 0,
            'Inbound Abandoned Calls' => 0,
        ];

        return ($this->zeroRecs($inbound, $zeroRec, $params));
    }

    private function outboundVolume($result, $params)
    {
        // extract Time and Outbound fields from array
        $outbound = [];
        foreach ($result as $rec) {
            foreach ($rec as $k => $v) {
                if ($k[0] !== 'O' && $k[0] !== 'T') {
                    unset($rec[$k]);
                }
            }
            $outbound[] = $rec;
        }

        // define recs with no data to compare against or insert if we need to fill in gaps
        $zeroRec = [
            'Time' => '',
            'Outbound Count' => 0,
            'Outbound Handled Calls' => 0,
            'Outbound Dropped Calls' => 0,
        ];

        return ($this->zeroRecs($outbound, $zeroRec, $params));
    }

    private function callDuration($result, $params)
    {
        // extract Time and Duration fields from array
        $duration = [];
        foreach ($result as $rec) {
            foreach ($rec as $k => $v) {
                if ($k[0] !== 'D' && $k[0] !== 'T') {
                    unset($rec[$k]);
                }
            }
            $duration[] = $rec;
        }

        // define recs with no data to compare against or insert if we need to fill in gaps
        $zeroRec = [
            'Time' => '',
            'Duration Inbound' => 0,
            'Duration Outbound' => 0,
        ];

        return ($this->zeroRecs($duration, $zeroRec, $params));
    }

    public function serviceLevel(Request $request)
    {
        $this->getSession($request);

        $campaign = $this->campaign;
        $dateFilter = $this->dateFilter;
        $timeZoneName = Auth::user()->tz;

        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $bind = [];

        $byHour = $this->byHour($dateFilter);

        // group by date/hour or just date
        if ($byHour) {
            $mapFunction = 'dateTimeToHour';
            $format = 'Y-m-d H:i:s.000';
            $modifier = "+1 hour";
            $xAxis = "DATEADD(HOUR, DATEPART(HOUR, CONVERT(datetimeoffset, DR.Date) AT TIME ZONE '$timeZoneName'),
            CAST(CAST(CONVERT(datetimeoffset, DR.Date) AT TIME ZONE '$timeZoneName' AS DATE) AS DATETIME))";
        } else {
            $mapFunction = 'dateTimeToDay';
            $format = 'Y-m-d 00:00:00.000';
            $modifier = "+1 day";
            $xAxis = "CAST(CAST(CONVERT(datetimeoffset, DR.Date) AT TIME ZONE '$timeZoneName' AS DATE) AS DATETIME)
            ";
        }

        $sql = "SELECT Time,
		'Handled Calls' = SUM(HandledCalls),
		'Total Inbound Calls' = SUM(Cnt)
		FROM (";
        $union = '';
        foreach ($this->databases as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $startDate;
            $bind['todate' . $i] = $endDate;
            $bind['answersecs' . $i] = $request->answer_secs ?? 20;

            $sql .= " $union SELECT $xAxis Time,
			'HandledCalls' = COUNT(CASE WHEN HoldTime < :answersecs$i AND CallStatus <> 'CR_HANGUP' THEN 1 ELSE NULL END),
			'Cnt' = COUNT(CallStatus)
			FROM [$db].[dbo].[DialingResults] DR
			WHERE CallType = 1
			AND CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound','TRANSFERRED','PARKED')
			AND DR.Date >= :fromdate$i
			AND DR.Date < :todate$i
            AND DR.GroupId = :groupid$i";

            list($where, $extrabind) = $this->campaignClause('DR', $i, $campaign);
            $sql .= " $where";
            $bind = array_merge($bind, $extrabind);

            $sql .= "
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
                'Rep' => '',
                'Handled Calls' => 0,
                'Total Inbound Calls' => 0,
            ],
        ];

        $result = $this->formatVolume($result, $params);

        $result = array_map(array(&$this, $mapFunction), $result);

        $time_labels = [];
        $total_calls = [];
        $handled_calls = [];
        $servicelevel = [];

        $tot_calls = 0;
        $tot_handled = 0;

        foreach ($result as $r) {
            $datetime = $r['Time'];
            $tot_calls += $r['Total Inbound Calls'];
            $tot_handled += $r['Handled Calls'];

            $level = $r['Total Inbound Calls'] == 0 ? 0 : round($r['Handled Calls'] / $r['Total Inbound Calls'] * 100);

            array_push($time_labels, $datetime);
            array_push($handled_calls, $r['Handled Calls']);
            array_push($total_calls, $r['Total Inbound Calls']);
            array_push($servicelevel, $level);
        }

        $avg_sl = $tot_calls == 0 ? 0 : round($tot_handled / $tot_calls * 100);

        return ['service_level' => [
            'time' => $time_labels,
            'total' => $total_calls,
            'handled_calls' => $handled_calls,
            'servicelevel' => $servicelevel,
            'avg' => $avg_sl,
        ]];
    }

    public function callDetails(Request $request)
    {
        $this->getSession($request);

        $campaign = $this->campaign;
        $dateFilter = $this->dateFilter;
        $timeZoneName = Auth::user()->tz;

        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $bind = [];

        $byHour = $this->byHour($dateFilter);

        // group by date/hour or just date
        if ($byHour) {
            $mapFunction = 'dateTimeToHour';
            $format = 'Y-m-d H:i:s.000';
            $modifier = "+1 hour";
            $xAxis = "DATEADD(HOUR, DATEPART(HOUR, CONVERT(datetimeoffset, Date) AT TIME ZONE '$timeZoneName'),
            CAST(CAST(CONVERT(datetimeoffset, Date) AT TIME ZONE '$timeZoneName' AS DATE) AS DATETIME))";
        } else {
            $mapFunction = 'dateTimeToDay';
            $format = 'Y-m-d 00:00:00.000';
            $modifier = "+1 day";
            $xAxis = "CAST(CAST(CONVERT(datetimeoffset, Date) AT TIME ZONE '$timeZoneName' AS DATE) AS DATETIME)";
        }

        $sql = "SELECT Time,
		'Call Count' = SUM(CallCount),
		'Call Time' = CAST(SUM(CallTime) AS INTEGER),
		'Wrap Up Time' = CAST(SUM(WrapUpTime) AS INTEGER)
		FROM (";
        $union = '';
        foreach ($this->databases as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $startDate;
            $bind['todate' . $i] = $endDate;

            $sql .= " $union SELECT $xAxis Time,
			'CallCount' = CASE WHEN AA.Action IN ('Call', 'ManualCall', 'InboundCall') THEN 1 ELSE 0 END,
			'CallTime' = CASE WHEN AA.Action IN ('Call', 'ManualCall', 'InboundCall') THEN AA.Duration ELSE 0 END,
			'WrapUpTime' = CASE WHEN AA.Action = 'Disposition' THEN AA.Duration ELSE 0 END
			FROM [$db].[dbo].[AgentActivity] AA
			WHERE Rep != ''
			AND AA.GroupId = :groupid$i
			AND AA.Date >= :fromdate$i
            AND AA.Date < :todate$i";

            list($where, $extrabind) = $this->campaignClause('DR', $i, $campaign);
            $sql .= " $where";
            $bind = array_merge($bind, $extrabind);

            $union = 'UNION ALL';
        }

        $sql .= ") tmp
		GROUP BY [Time]
		ORDER BY [Time]";

        $result1 = $this->runSql($sql, $bind);

        // We have to get HoldTime from another table, then merge it in.  sigh....
        $bind = [];

        $sql = "SELECT Time,
		'Hold Time' = SUM(HoldTime),
		'Max Hold' = MAX(HoldTime)
		FROM (";
        $union = '';
        foreach ($this->databases as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $startDate;
            $bind['todate' . $i] = $endDate;

            $sql .= " $union SELECT $xAxis Time,
			'HoldTime' = CASE WHEN HoldTime <= 0 THEN 0 ELSE HoldTime END
			FROM [$db].[dbo].[DialingResults] DR
			WHERE DR.CallType = 1
			AND DR.Rep != ''
			AND DR.CallStatus IS NOT NULL
			AND DR.CallStatus NOT IN('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound','TRANSFERRED','PARKED')
			AND DR.GroupId = :groupid$i
			AND Date >= :fromdate$i
            AND Date < :todate$i";

            list($where, $extrabind) = $this->campaignClause('DR', $i, $campaign);
            $sql .= " $where";
            $bind = array_merge($bind, $extrabind);

            $union = 'UNION ALL';
        }

        $sql .= ") tmp
		GROUP BY [Time]
		ORDER BY [Time]";

        $result2 = $this->runSql($sql, $bind);

        $params = [
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'modifier' => $modifier,
            'byHour' => $byHour,
            'format' => $format,
            'zeroRec' => [
                'Time' => '',
                'Call Count' => 0,
                'Call Time' => 0,
                'Wrap Up Time' => 0,
                'Hold Time' => 0,
                'Max Hold' => 0,
            ],
        ];

        $result = $this->combineArrays($result1, $result2, $params['zeroRec']);

        $result = $this->formatVolume($result, $params);

        // now format the xAxis datetimes
        $result = array_map(array(&$this, $mapFunction), $result);

        $avg_handle_time = [];
        $total_handle_time = 0;

        $time_labels = [];
        $calls = [];
        $num_calls = 0;
        $calltimes = 0;
        $avg_ht = 0;
        $wrapup = [];
        $holdtime = [];
        $maxhold = [];

        foreach ($result as $r) {
            array_push($time_labels, $r['Time']);
            array_push($calls, $r['Call Time']);
            array_push($holdtime, $r['Hold Time']);
            array_push($maxhold, $r['Max Hold']);
            array_push($wrapup, $r['Wrap Up Time']);

            $calltimes += $r['Call Time'];
            $num_calls += $r['Call Count'];
            $total_handle_time += $r['Call Time'] + $r['Hold Time'] + $r['Wrap Up Time'];

            $avg = empty($r['Call Count']) ? 0 : round(($r['Call Time'] + $r['Hold Time'] + $r['Wrap Up Time']) / $r['Call Count']);
            array_push($avg_handle_time, $avg);
        }

        if ($num_calls > 0) {
            $avg_ht = round($total_handle_time / $num_calls);
            $avg_call_time = round($calltimes / $num_calls);
        } else {
            $avg_ht = 0;
            $avg_call_time = 0;
        }

        return ['call_details' => [
            'datetime' => $time_labels,
            'calls' => $calls,
            'hold_time' => $holdtime,
            'max_hold' => $maxhold,
            'wrapup_time' => $wrapup,
            'avg_handle_time' => $avg_handle_time,
            'avg_call_time' => $avg_call_time,
            'avg_ht' => $avg_ht,
        ]];
    }

    private function formatVolume($result, $params)
    {
        // define recs with no data to compare against or insert if we need to fill in gaps
        return ($this->zeroRecs($result, $params['zeroRec'], $params));
    }

    private function combineArrays($arr, $hold, $zeroRec)
    {
        // This is certainly not the most efficient way to do this....
        // first, go thru time recs and add hold time if we can find one
        foreach ($arr as &$rec) {
            $found = array_search($rec['Time'], array_column($hold, 'Time'));
            if ($found === false) {
                $rec['Hold Time'] = 0;
                $rec['Max Hold'] = 0;
            } else {
                $rec['Hold Time'] = $hold[$found]['Hold Time'];
                $rec['Max Hold'] = $hold[$found]['Max Hold'];
            }
        }

        // Now, go thru hold recs and add a blank time rec
        $newRecs = [];
        foreach ($hold as $holdRec) {
            $found = array_search($holdRec['Time'], array_column($arr, 'Time'));
            if ($found === false) {
                $zeroRec['Time'] = $holdRec['Time'];
                $zeroRec['Hold Time'] = $holdRec['Hold Time'];
                $zeroRec['Max Hold'] = $holdRec['Max Hold'];
                $newRecs[] = $zeroRec;
            }
        }

        return array_merge($arr, $newRecs);
    }

    public function agentCallTime(Request $request)
    {

        $this->getSession($request);

        $campaign = $this->campaign;
        $dateFilter = $this->dateFilter;

        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $bind = [];

        $sql = "SELECT Rep,
		'Total Calls' = SUM(Cnt),
		'Duration' = SUM(Duration)
		FROM (";
        $union = '';
        foreach ($this->databases as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $startDate;
            $bind['todate' . $i] = $endDate;

            $sql .= " $union SELECT DR.Rep,
			'Cnt' = COUNT(DR.CallStatus),
			'Duration' = SUM(DR.HandleTime)
			FROM [$db].[dbo].[DialingResults] DR
			WHERE DR.CallStatus NOT IN('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound')
			AND DR.CallType NOT IN ('7','8')
			AND DR.HandleTime != 0
			AND DR.Duration != 0
			AND DR.GroupId = :groupid$i
			AND DR.Date >= :fromdate$i
            AND DR.Date < :todate$i";

            list($where, $extrabind) = $this->campaignClause('DR', $i, $campaign);
            $sql .= " $where";
            $bind = array_merge($bind, $extrabind);

            $sql .= "
                GROUP BY(DR.Rep)";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp
		GROUP BY Rep
		ORDER BY Rep";

        $result = $this->runSql($sql, $bind);

        $agent_labels = [];
        $total_calls = [];
        $call_duration = [];
        $cnt = 0;
        $avg_ct = 0;
        $avg_cc = 0;

        foreach ($result as $r) {
            if ($r['Rep'] != Null) {
                array_push($agent_labels, $r['Rep']);
                array_push($total_calls, $r['Total Calls']);
                array_push($call_duration, round($r['Duration']));
                $avg_ct += round($r['Duration']);
                $avg_cc += $r['Total Calls'];
                $cnt++;
            }
        }

        $avg_ct = $avg_ct > 0 ? round($avg_ct / $avg_cc) : 0;
        $avg_cc = $avg_cc > 0 ? round($avg_cc / $cnt) : 0;

        return ['agent_calltime' => [
            'rep' => $agent_labels,
            'duration' => $call_duration,
            'total_calls' => $total_calls,
            'avg_ct' => $avg_ct,
            'avg_cc' => $avg_cc,
        ]];
    }
}
