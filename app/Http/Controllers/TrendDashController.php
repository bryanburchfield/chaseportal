<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Campaign;
use \App\Traits\DashTraits;

class TrendDashController extends Controller
{
    use DashTraits;

    public function index(Request $request)
    {
        $this->getSession($request);

        $groupId = Auth::user()->group_id;
        $campaigns = Campaign::where('GroupId', $groupId)->where('IsActive', 1)->pluck('CampaignName')->toArray();
        natcasesort($campaigns);
        array_unshift($campaigns, 'Total');

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

        $inbound_time_labels = [];
        $total_inbound_calls = [];
        $inbound_voicemails = [];
        $inbound_abandoned = [];
        $inbound_handled = [];
        $inbound_duration = [];

        $outbound_time_labels = [];
        $total_outbound_calls = [];
        $outbound_handled = [];
        $outbound_dropped = [];
        $outbound_duration = [];
        $duration_time = [];
        $new_result = [];
        $total_outbound_duration = 0;
        $total_inbound_duration = 0;

        foreach ($result[0] as $r) {
            if ($this->byHour($this->dateFilter)) {
                $datetime = date("g:i", strtotime($r['Time']));
            } else {
                $datetime = date("n/j/y", strtotime($r['Time']));
            }

            array_push($inbound_time_labels, $datetime);
            array_push($total_inbound_calls, $r['Inbound Count']);
            array_push($inbound_voicemails, $r['Inbound Voicemails']);
            array_push($inbound_abandoned, $r['Inbound Abandoned Calls']);
            array_push($inbound_handled, $r['Inbound Handled Calls']);
        }

        foreach ($result[1] as $r) {
            if ($this->byHour($this->dateFilter)) {
                $datetime = date("g:i", strtotime($r['Time']));
            } else {
                $datetime = date("n/j/y", strtotime($r['Time']));
            }

            array_push($outbound_time_labels, $datetime);
            array_push($total_outbound_calls, $r['Outbound Count']);
            array_push($outbound_handled, $r['Outbound Handled Calls']);
            array_push($outbound_dropped, $r['Outbound Dropped Calls']);
        }

        foreach ($result[2] as $r) {
            if ($this->byHour($this->dateFilter)) {
                $datetime = date("g:i", strtotime($r['Time']));
            } else {
                $datetime = date("n/j/y", strtotime($r['Time']));
            }

            $r['Duration Inbound'] = round($r['Duration Inbound'] / 60);
            $r['Duration Outbound'] = round($r['Duration Outbound'] / 60);
            array_push($duration_time, $r['Time']);
            array_push($inbound_duration, $r['Duration Inbound']);
            array_push($outbound_duration, $r['Duration Outbound']);

            $total_inbound_duration += $r['Duration Inbound'];
            $total_outbound_duration += $r['Duration Outbound'];
        }

        $total = $total_inbound_duration + $total_outbound_duration;
        $details = $this->filterDetails();

        $new_result = [
            'inbound_time_labels' => $inbound_time_labels,
            'outbound_time_labels' => $outbound_time_labels,
            'total_inbound_calls' => $total_inbound_calls,
            'inbound_voicemails' => $inbound_voicemails,
            'inbound_abandoned' => $inbound_abandoned,
            'inbound_handled' => $inbound_handled,
            'inbound_duration' => $inbound_duration,
            'outbound_handled' => $outbound_handled,
            'total_outbound_calls' => $total_outbound_calls,
            'outbound_dropped' => $outbound_dropped,
            'outbound_duration' => $outbound_duration,
            'total_inbound_duration' => $total_inbound_duration,
            'total_outbound_duration' => $total_outbound_duration,
            'duration_time' => $duration_time,
            'total' => $total,
            'details' => $details,
        ];

        return ['call_volume' => $new_result];
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

        $bind = [
            'groupid' => Auth::user()->group_id,
            'fromdate' => $startDate,
            'todate' => $endDate,
        ];

        $sql = "SELECT
        Time,
        SUM([Inbound Count]) AS 'Inbound Count',
        SUM([Inbound Handled Calls]) AS 'Inbound Handled Calls',
        SUM([Inbound Voicemails]) AS 'Inbound Voicemails',
        SUM([Inbound Abandoned Calls]) AS 'Inbound Abandoned Calls',
        SUM([Inbound Dropped Calls]) AS 'Inbound Dropped Calls',
        SUM([Duration Inbound]) AS 'Duration Inbound',
        SUM([Outbound Count]) AS 'Outbound Count',
        SUM([Outbound Handled Calls]) AS 'Outbound Handled Calls',
        SUM([Outbound Abandoned Calls]) AS 'Outbound Abandoned Calls',
        SUM([Outbound Dropped Calls]) AS 'Outbound Dropped Calls',
        SUM([Duration Outbound]) AS 'Duration Outbound'
        FROM (";

        $union = '';
        foreach (Auth::user()->getDatabaseArray() as $i => $db) {
            $sql .= " $union SELECT $xAxis as 'Time',
    'Inbound Count' = SUM(CASE WHEN DR.CallType IN ('1','11') THEN 1 ELSE 0 END),
    'Inbound Handled Calls' = SUM(CASE WHEN DR.CallType IN ('1','11') AND DR.CallStatus NOT IN ( 'CR_CEPT', 'CR_CNCT/CON_PAMD', 'CR_NOANS', 'CR_NORB', 'CR_BUSY',
    'CR_DROPPED', 'CR_FAXTONE', 'CR_FAILED', 'CR_DISCONNECTED',
    'CR_HANGUP', 'Inbound Voicemail') THEN 1 ELSE 0 END),
    'Inbound Voicemails' = SUM(CASE WHEN DR.CallType IN ('1','11') AND DR.CallStatus='Inbound Voicemail' THEN 1 ELSE 0 END),
    'Inbound Abandoned Calls' = SUM(CASE WHEN DR.CallType IN ('1','11') AND DR.CallStatus='CR_HANGUP' THEN 1 ELSE 0 END),
    'Inbound Dropped Calls' = SUM(CASE WHEN DR.CallType IN ('1','11') AND DR.CallStatus='CR_DROPPED' THEN 1 ELSE 0 END),
    'Duration Inbound' = SUM(CASE WHEN DR.CallType IN ('1','11') THEN DR.Duration ELSE 0 END),
    'Outbound Count' = SUM(CASE WHEN DR.CallType NOT IN ('1','11') THEN 1 ELSE 0 END),
    'Outbound Handled Calls' = SUM(CASE WHEN DR.CallType NOT IN ('1','11') AND DR.CallStatus NOT IN ( 'CR_CEPT', 'CR_CNCT/CON_PAMD', 'CR_NOANS', 'CR_NORB', 'CR_BUSY',
    'CR_DROPPED', 'CR_FAXTONE', 'CR_FAILED', 'CR_DISCONNECTED',
    'CR_HANGUP', 'Inbound Voicemail') THEN 1 ELSE 0 END),
    'Outbound Abandoned Calls' = SUM(CASE WHEN DR.CallType NOT IN ('1','11') AND DR.CallStatus='CR_HANGUP' THEN 1 ELSE 0 END),
    'Outbound Dropped Calls' = SUM(CASE WHEN DR.CallType NOT IN ('1','11') AND DR.CallStatus='CR_DROPPED' THEN 1 ELSE 0 END),
    'Duration Outbound' = SUM(CASE WHEN DR.CallType NOT IN ('1','11') THEN DR.Duration ELSE 0 END)
            FROM [$db].[dbo].[DialingResults] DR
            WHERE DR.CallType NOT IN ('7','8')
            AND DR.CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound')
            AND Duration > 0
            AND DR.Date >= :fromdate
            AND DR.Date < :todate
            AND DR.GroupId = :groupid";

            if (!empty($campaign) && $campaign != 'Total') {
                $sql .= " AND DR.Campaign = :campaign$i";
                $bind['campaign' . $i] = $campaign;
            }

            $sql .= "
            GROUP BY $xAxis";

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
        $durResult = $this->callDuration($result, $params);

        // now format the xAxis datetimes and return the results
        return [
            array_map(array(&$this, $mapFunction), $inResult),
            array_map(array(&$this, $mapFunction), $outResult),
            array_map(array(&$this, $mapFunction), $durResult),
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
            'Inbound Dropped Calls' => 0,
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
            'Outbound Abandoned Calls' => 0,
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

        $bind = [
            'fromdate' => $startDate,
            'todate' => $endDate,
            'groupid' => Auth::user()->group_id,
            'answersecs' => $request->answer_secs ?? 20,
        ];

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
        foreach (Auth::user()->getDatabaseArray() as $i => $db) {
            $sql .= " $union SELECT $xAxis Time,
			'HandledCalls' = COUNT(CASE WHEN HoldTime < :answersecs AND CallStatus <> 'CR_HANGUP' THEN 1 ELSE NULL END),
			'Cnt' = COUNT(CallStatus)
			FROM [$db].[dbo].[DialingResults] DR
			WHERE CallType = 1
			AND CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound','TRANSFERRED','PARKED')
			AND DR.Date >= :fromdate
			AND DR.Date < :todate
            AND DR.GroupId = :groupid ";

            if (!empty($campaign) && $campaign != 'Total') {
                $sql .= " AND DR.Campaign = :campaign$i";
                $bind['campaign' . $i] = $campaign;
            }

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
        $new_result = [];

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

        $new_result = [
            'time' => $time_labels,
            'total' => $total_calls,
            'handled_calls' => $handled_calls,
            'servicelevel' => $servicelevel,
            'avg' => $avg_sl,
        ];

        return ['service_level' => $new_result];
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

        $bind = [
            'groupid' => Auth::user()->group_id,
            'fromdate' => $startDate,
            'todate' => $endDate,
        ];

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
        foreach (Auth::user()->getDatabaseArray() as $i => $db) {
            $sql .= " $union SELECT $xAxis Time,
			'CallCount' = SUM(CASE WHEN AA.Action IN ('Call', 'ManualCall', 'InboundCall') THEN 1 ELSE 0 END),
			'CallTime' = SUM(CASE WHEN AA.Action IN ('Call', 'ManualCall', 'InboundCall') THEN AA.Duration ELSE 0 END),
			'WrapUpTime' = SUM(CASE WHEN AA.Action = 'Disposition' THEN AA.Duration ELSE 0 END)
			FROM [$db].[dbo].[AgentActivity] AA
			WHERE Rep != ''
			AND AA.GroupId = :groupid
			AND AA.Date >= :fromdate
            AND AA.Date < :todate";

            if (!empty($campaign) && $campaign != 'Total') {
                $sql .= " AND DR.Campaign = :campaign$i";
                $bind['campaign' . $i] = $campaign;
            }

            $sql .= "
                GROUP BY $xAxis";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp
		GROUP BY [Time]
		ORDER BY [Time]";

        $result1 = $this->runSql($sql, $bind);

        // We have to get HoldTime from another table, then merge it in.  sigh....
        $sql = "SELECT Time,
		'Hold Time' = SUM(HoldTime),
		'Max Hold' = MAX(HoldTime)
		FROM (";
        $union = '';
        foreach (Auth::user()->getDatabaseArray() as $i => $db) {
            $sql .= " $union SELECT $xAxis Time,
			'HoldTime' = SUM(CASE WHEN HoldTime <= 0 THEN 0 ELSE HoldTime END)
			FROM [$db].[dbo].[DialingResults] DR
			WHERE DR.CallType = 1
			AND DR.Rep != ''
			AND DR.CallStatus IS NOT NULL
			AND DR.CallStatus NOT IN('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound','TRANSFERRED','PARKED')
			AND DR.GroupId = :groupid
			AND Date >= :fromdate
            AND Date < :todate";

            if (!empty($campaign) && $campaign != 'Total') {
                $sql .= " AND DR.Campaign = :campaign$i";
                $bind['campaign' . $i] = $campaign;
            }

            $sql .= "
                GROUP BY $xAxis";

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

        // Now calculate avg handle time
        foreach ($result as &$rec) {
            $rec['Avg Handle Time'] = (empty($rec['Call Count'])) ? 0 : intval(($rec['Call Time'] + $rec['Wrap Up Time'] + $rec['Hold Time']) / $rec['Call Count']);
        }

        $avg_handle_time = [];

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
            array_push($calls, round($r['Call Time'] / 60));
            array_push($holdtime, round($r['Hold Time'] / 60));
            array_push($maxhold, round($r['Max Hold'] / 60));
            array_push($wrapup, round($r['Wrap Up Time'] / 60));
            $calltimes += round($r['Call Time'] / 60);
            $num_calls += $r['Call Count'];

            $avg = empty($r['Call Count']) ? 0 : round(($r['Call Time'] / 60 + $r['Hold Time'] / 60 + $r['Wrap Up Time'] / 60) / $r['Call Count']);
            array_push($avg_handle_time, $avg);

            $avg_ht += $avg;
        }


        $avg_ht = round($avg_ht / count($result[0]));
        $avg_call_time = !empty($num_calls) ? round($calltimes / $num_calls) : 0;

        $new_result = [
            'datetime' => $time_labels,
            'calls' => $calls,
            'hold_time' => $holdtime,
            'max_hold' => $maxhold,
            'wrapup_time' => $wrapup,
            'avg_handle_time' => $avg_handle_time,
            'avg_call_time' => $avg_call_time,
            'avg_ht' => $avg_ht,
        ];

        return ['call_details' => $new_result];
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

        $bind = [
            'groupid' => Auth::user()->group_id,
            'fromdate' => $startDate,
            'todate' => $endDate,
        ];

        $sql = "SELECT Rep,
		'Total Calls' = SUM(Cnt),
		'Duration' = SUM(Duration)
		FROM (";
        $union = '';
        foreach (Auth::user()->getDatabaseArray() as $i => $db) {
            $sql .= " $union SELECT DR.Rep,
			'Cnt' = COUNT(DR.CallStatus),
			'Duration' = SUM(DR.HandleTime)
			FROM [$db].[dbo].[DialingResults] DR
			WHERE DR.CallStatus NOT IN('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound')
			AND DR.CallType NOT IN ('7','8')
			AND DR.HandleTime != 0
			AND DR.Duration != 0
			AND DR.GroupId = :groupid
			AND DR.Date >= :fromdate
            AND DR.Date < :todate";

            if (!empty($campaign) && $campaign != 'Total') {
                $sql .= " AND DR.Campaign = :campaign$i";
                $bind['campaign' . $i] = $campaign;
            }

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
                array_push($call_duration, round($r['Duration'] / 60));
                $avg_ct += round($r['Duration'] / 60);
                $avg_cc += $r['Total Calls'];
                $cnt++;
            }
        }

        $avg_ct = $avg_ct > 0 ? round($avg_ct / $avg_cc) : 0;
        $avg_cc = $avg_cc > 0 ? round($avg_cc / $cnt) : 0;

        $new_result = [
            'rep' => $agent_labels,
            'duration' => $call_duration,
            'total_calls' => $total_calls,
            'avg_ct' => $avg_ct,
            'avg_cc' => $avg_cc,
        ];

        return ['agent_calltime' => $new_result];
    }
}
