<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Campaign;

class AdminDashController extends Controller
{
    use DashTraits;

    public function index(Request $request)
    {
        $this->getSession($request);

        $groupId = Auth::user()->group_id;
        $campaigns = Campaign::where('GroupId', $groupId)->where('IsActive', 1)->pluck('CampaignName')->toArray();
        natcasesort($campaigns);
        array_unshift($campaigns, 'Total');

        $jsfile[] = "admindash.js";
        $cssfile[] = "admindash.css";

        $data = [
            'isApi' => $this->isApi,
            'campaign' => $this->campaign,
            'datefilter' => $this->dateFilter,
            'inorout' => $this->inorout,
            'campaign_list' => $campaigns,
            'curdash' => 'admindash',
            'jsfile' => $jsfile,
            'cssfile' => $cssfile,
        ];
        return view('admindash')->with($data);
    }

    public function callVolume(Request $request)
    {
        $this->getSession($request);

        $result = $this->getCallVolume();
        $prev_result = $this->getCallVolume(true);

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

        $total_duration = 0;
        $prev_total_duration = 0;

        foreach ($result[0] as $r) {

            if (!strpos($r['Time'], ':')) {
                $datetime = date("n/j/y", strtotime($r['Time']));
            } else {
                $datetime = date("g:i", strtotime($r['Time']));
            }

            array_push($inbound_time_labels, $datetime);
            array_push($total_inbound_calls, $r['Inbound Count']);
            array_push($inbound_voicemails, $r['Inbound Voicemails']);
            array_push($inbound_abandoned, $r['Inbound Abandoned Calls']);
            array_push($inbound_handled, $r['Inbound Handled Calls']);
        }

        foreach ($result[1] as $r) {

            if (!strpos($r['Time'], ':')) {
                $datetime = date("n/j/y", strtotime($r['Time']));
            } else {
                $datetime = date("g:i", strtotime($r['Time']));
            }

            array_push($outbound_time_labels, $datetime);
            array_push($total_outbound_calls, $r['Outbound Count']);
            array_push($outbound_handled, $r['Outbound Handled Calls']);
            array_push($outbound_dropped, $r['Outbound Dropped Calls']);
        }

        foreach ($result[2] as $r) {
            if (!strpos($r['Time'], ':')) {
                $datetime = date("n/j/y", strtotime($r['Time']));
            } else {
                $datetime = date("g:i", strtotime($r['Time']));
            }

            $total_duration += $r['Duration Inbound'] + $r['Duration Outbound'];

            $r['Duration Inbound'] = round($r['Duration Inbound'] / 60);
            $r['Duration Outbound'] = round($r['Duration Outbound'] / 60);
            array_push($duration_time, $datetime);
            array_push($inbound_duration, $r['Duration Inbound']);
            array_push($outbound_duration, $r['Duration Outbound']);

            $total_inbound_duration += $r['Duration Inbound'];
            $total_outbound_duration += $r['Duration Outbound'];
        }

        foreach ($prev_result[2] as $r) {
            $prev_total_duration += $r['Duration Inbound'] + $r['Duration Outbound'];
        }

        if ($prev_total_duration == 0) {
            $pctdiff = null;
            $pctsign = null;
            $ntc = 1;  // nothing to compare
        } else {
            $pctdiff = ($total_duration - $prev_total_duration) / $prev_total_duration * 100;
            $pctsign = $pctdiff < 0 ? 0 : 1;
            $pctdiff = round(abs($pctdiff));
            $ntc = 0;
        }

        // this uses mins instead of secs
        $total_duration = $total_inbound_duration + $total_outbound_duration;

        $new_result['inbound_time_labels'] = $inbound_time_labels;
        $new_result['outbound_time_labels'] = $outbound_time_labels;
        $new_result['total_inbound_calls'] = $total_inbound_calls;
        $new_result['inbound_voicemails'] = $inbound_voicemails;
        $new_result['inbound_abandoned'] = $inbound_abandoned;
        $new_result['inbound_handled'] = $inbound_handled;
        $new_result['inbound_duration'] = $inbound_duration;
        $new_result['outbound_handled'] = $outbound_handled;
        $new_result['total_outbound_calls'] = $total_outbound_calls;
        $new_result['outbound_dropped'] = $outbound_dropped;
        $new_result['outbound_handled'] = $outbound_handled;
        $new_result['outbound_duration'] = $outbound_duration;
        $new_result['total_inbound_duration'] = $total_inbound_duration;
        $new_result['total_outbound_duration'] = $total_outbound_duration;
        $new_result['duration_time'] = $duration_time;
        $new_result['total'] = $total_duration;
        $new_result['pct_change'] = $pctdiff;
        $new_result['pct_sign'] = $pctsign;
        $new_result['ntc'] = $ntc;

        $return['call_volume'] = $new_result;
        echo json_encode($return);
    }

    private function getCallVolume($prev = false)
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
            $xAxis = "DATEADD(HOUR, DATEPART(HOUR, CONVERT(datetimeoffset, Date) AT TIME ZONE '$timeZoneName'),
            CAST(CAST(CONVERT(datetimeoffset, Date) AT TIME ZONE '$timeZoneName' AS DATE) AS DATETIME))";
        } else {
            $mapFunction = 'dateTimeToDay';
            $format = 'Y-m-d 00:00:00.000';
            $modifier = "+1 day";
            $xAxis = "CAST(CAST(CONVERT(datetimeoffset, Date) AT TIME ZONE '$timeZoneName' AS DATE) AS DATETIME)";
        }

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

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

            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $startDate;
            $bind['todate' . $i] = $endDate;

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
            AND DR.Date >= :fromdate$i
            AND DR.Date < :todate$i
            AND DR.GroupId = :groupid$i ";

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

        return [
            $inResult,
            $outResult,
            $durResult,
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

    public function completedCalls(Request $request)
    {
        $this->getSession($request);

        $completed_calls = $this->getCompletedCalls();
        $prev_completed_calls = $this->getCompletedCalls(true);

        $details = $this->filterDetails();

        $inbound = ['1', '11'];

        $total_completed_calls = 0;
        $prev_total_completed_calls = 0;
        $outbound_completed_calls = 0;
        $inbound_completed_calls = 0;

        foreach ($completed_calls as $call) {
            $total_completed_calls += $call['Agent Calls'];
            if (in_array($call['CallType'], $inbound)) {
                $inbound_completed_calls += $call['Agent Calls'];
            } else {
                $outbound_completed_calls += $call['Agent Calls'];
            }
        }

        foreach ($prev_completed_calls as $call) {
            $prev_total_completed_calls += $call['Agent Calls'];
        }

        if ($prev_total_completed_calls == 0) {
            $pctdiff = null;
            $pctsign = null;
            $ntc = 1;  // nothing to compare
        } else {
            $pctdiff = ($total_completed_calls - $prev_total_completed_calls) / $prev_total_completed_calls * 100;
            $pctsign = $pctdiff < 0 ? 0 : 1;
            $pctdiff = round(abs($pctdiff), 0);
            $ntc = 0;
        }

        $return['completed_calls'] =  [
            'total' => $total_completed_calls,
            'outbound' => $outbound_completed_calls,
            'inbound' => $inbound_completed_calls,
            'details' => $details,
            'pct_change' => $pctdiff,
            'pct_sign' => $pctsign,
            'ntc' => $ntc,
        ];

        echo json_encode($return);
    }

    private function getCompletedCalls($prev = false)
    {
        $campaign = $this->campaign;
        $dateFilter = $this->dateFilter;

        if ($prev) {
            list($fromDate, $toDate) = $this->previousDateRange($dateFilter);
        } else {
            list($fromDate, $toDate) = $this->dateRange($dateFilter);
        }

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $sql = 'SELECT CallType, SUM([Agent Calls]) as [Agent Calls] FROM (';
        $union = '';
        foreach (Auth::user()->getDatabaseArray() as $i => $db) {

            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $startDate;
            $bind['todate' . $i] = $endDate;

            $sql .= " $union SELECT DR.CallType AS 'CallType',
                COUNT(DR.CallStatus) AS 'Agent Calls'
                FROM [$db].[dbo].[DialingResults] DR
                WHERE DR.CallType NOT IN ('7','8')
                AND DR.CallStatus NOT IN (
                    ' ',
                    'CR_BUSY',
                    'CR_CEPT',
                    'CR_CNCT/CON_CAD',
                    'CR_CNCT/CON_PAMD',
                    'CR_CNCT/CON_PVD',
                    'CR_DISCONNECTED',
                    'CR_DROPPED',
                    'CR_FAILED',
                    'CR_FAXTONE',
                    'CR_HANGUP',
                    'CR_NOANS',
                    'CR_NORB',
                    'Inbound',
                    'Inbound Voicemail'
                )
                AND DR.Date >= :fromdate$i
                AND DR.Date < :todate$i
                AND DR.GroupId = :groupid$i ";

            if (!empty($campaign) && $campaign != 'Total') {
                $sql .= " AND DR.Campaign = :campaign$i";
                $bind['campaign' . $i] = $campaign;
            }

            $sql .= "
                GROUP BY DR.CallType";

            $union = 'UNION ALL';
        }
        $sql .= ") tmp GROUP BY CallType";

        $result = $this->runSql($sql, $bind);

        return $result;
    }

    public function avgHoldTime(Request $request)
    {
        $this->getSession($request);

        $average_hold_time = $this->getAvgHoldTime();
        $prev_average_hold_time = $this->getAvgHoldTime(true);

        if ($average_hold_time['Total Calls'] == 0) {
            $avg_hold_time = 0;
        } else {
            $avg_hold_time = $average_hold_time['Hold Secs'] / $average_hold_time['Total Calls'];
        }

        if ($prev_average_hold_time['Total Calls'] == 0) {
            $prev_avg_hold_time = 0;
        } else {
            $prev_avg_hold_time = $prev_average_hold_time['Hold Secs'] / $prev_average_hold_time['Total Calls'];
        }

        if ($prev_avg_hold_time == 0) {
            $pctdiff = null;
            $pctsign = null;
            $ntc = 1;  // nothing to compare
        } else {
            $pctdiff = ($avg_hold_time - $prev_avg_hold_time) / $prev_avg_hold_time * 100;
            $pctsign = $pctdiff < 0 ? 0 : 1;
            $pctdiff = round(abs($pctdiff), 0);
            $ntc = 0;
        }

        $avg_hold_time = secondsToHms($avg_hold_time);
        $total_hold_time = secondsToHms($average_hold_time['Hold Secs']);

        $return['average_hold_time'] = [
            'avg_hold_time' => $avg_hold_time,
            'total_hold_time' => $total_hold_time,
            'pct_change' => $pctdiff,
            'pct_sign' => $pctsign,
            'ntc' => $ntc,
        ];

        echo json_encode($return);
    }

    private function getAvgHoldTime($prev = false)
    {
        $campaign = $this->campaign;
        $dateFilter = $this->dateFilter;

        if ($prev) {
            list($fromDate, $toDate) = $this->previousDateRange($dateFilter);
        } else {
            list($fromDate, $toDate) = $this->dateRange($dateFilter);
        }

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $sql = "SELECT
        SUM(Cnt) as 'Total Calls',
        SUM(HoldTime) as 'Hold Secs'
        FROM (";
        $union = '';
        foreach (Auth::user()->getDatabaseArray() as $i => $db) {

            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $startDate;
            $bind['todate' . $i] = $endDate;

            $sql .= " $union SELECT 'Cnt' = COUNT(CallStatus),
                'HoldTime' = SUM(HoldTime)
                FROM [$db].[dbo].[DialingResults] DR
                WHERE CallType = 1
                AND CallStatus NOT IN('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound','TRANSFERRED','PARKED')
                AND HoldTime >= 0
                AND DR.Date >= :fromdate$i
                AND DR.Date < :todate$i
                AND DR.GroupId = :groupid$i";

            if (!empty($campaign) && $campaign != 'Total') {
                $sql .= " AND DR.Campaign = :campaign$i";
                $bind['campaign' . $i] = $campaign;
            }

            $union = 'UNION ALL';
        }
        $sql .= ") tmp";

        $result = $this->runSql($sql, $bind);

        return $result[0];
    }

    public function abandonRate(Request $request)
    {
        $this->getSession($request);

        $abandon_rate = $this->getAbandonRate();
        $prev_abandon_rate = $this->getAbandonRate(true);

        $abandon_pct = ($abandon_rate['Calls'] == 0) ? 0 : $abandon_rate['Abandoned'] / $abandon_rate['Calls'] * 100;
        $prev_abandon_pct = ($prev_abandon_rate['Calls'] == 0) ? 0 : $prev_abandon_rate['Abandoned'] / $prev_abandon_rate['Calls'] * 100;

        if ($prev_abandon_pct == 0) {
            $pctdiff = null;
            $pctsign = null;
            $ntc = 1;  // nothing to compare
        } else {
            $pctdiff = ($abandon_pct - $prev_abandon_pct) / $prev_abandon_pct * 100;
            $pctsign = $pctdiff < 0 ? 0 : 1;
            $pctdiff = round(abs($pctdiff), 0);
            $ntc = 0;
        }

        $abandon_pct = round($abandon_pct, 2) . '%';

        $return['abandon_rate'] = [
            'abandon_calls' => $abandon_rate['Abandoned'],
            'abandon_rate' => $abandon_pct,
            'pct_change' => $pctdiff,
            'pct_sign' => $pctsign,
            'ntc' => $ntc,
        ];

        echo json_encode($return);
    }

    private function getAbandonRate($prev = false)
    {
        $campaign = $this->campaign;
        $dateFilter = $this->dateFilter;

        if ($prev) {
            list($fromDate, $toDate) = $this->previousDateRange($dateFilter);
        } else {
            list($fromDate, $toDate) = $this->dateRange($dateFilter);
        }

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $sql = "SELECT
        'Calls' = SUM(Calls),
        'Abandoned' = SUM(Abandoned)
        FROM (";
        $union = '';
        foreach (Auth::user()->getDatabaseArray() as $i => $db) {

            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $startDate;
            $bind['todate' . $i] = $endDate;

            $sql .= " $union SELECT
            'Calls' = COUNT(CallStatus),
            'Abandoned' = SUM(CASE WHEN CallStatus='CR_HANGUP' THEN 1 ELSE 0 END)
            FROM [$db].[dbo].[DialingResults] DR
            WHERE CallType = 1
            AND CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound','TRANSFERRED','PARKED')
            AND DR.Date >= :fromdate$i
            AND DR.Date < :todate$i
            AND DR.GroupId = :groupid$i";

            if (!empty($campaign) && $campaign != 'Total') {
                $sql .= " AND DR.Campaign = :campaign$i";
                $bind['campaign' . $i] = $campaign;
            }

            $sql .= "
            GROUP BY Campaign";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp";

        $result = $this->runSql($sql, $bind);

        return $result[0];
    }

    public function agentCallCount(Request $request)
    {
        $this->getSession($request);

        $campaign = $this->campaign;
        $dateFilter = $this->dateFilter;

        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $sql = "SET NOCOUNT ON;
        
        SELECT Rep, Campaign,
        'Count' = SUM([Count]),
        'Duration' = SUM(Duration)
        INTO #temp
        FROM (";
        $union = '';
        foreach (Auth::user()->getDatabaseArray() as $i => $db) {

            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $startDate;
            $bind['todate' . $i] = $endDate;

            $sql .= " $union SELECT DR.Rep, DR.Campaign,
            'Count' = COUNT(DR.CallStatus),
            'Duration' = SUM(DR.Duration)
            FROM [$db].[dbo].[DialingResults] DR
            WHERE DR.CallType NOT IN (7,8)
            AND DR.CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound')
            AND Duration <> 0
            AND DR.Date >= :fromdate$i
            AND DR.Date < :todate$i
            AND DR.GroupId = :groupid$i ";

            if (!empty($campaign) && $campaign != 'Total') {
                $sql .= " AND DR.Campaign = :campaign$i";
                $bind['campaign' . $i] = $campaign;
            }

            $sql .= " GROUP BY DR.Rep, DR.Campaign";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp GROUP BY Rep, Campaign;
        
        SELECT * FROM #temp
        ORDER BY Rep, Campaign;;
        
        SELECT Rep, SUM(Count) as Count, SUM(Duration) as Duration
        FROM #temp
        GROUP BY Rep
        ORDER by Rep";

        $result = $this->runMultiSql($sql, $bind);

        $bycamp = $result[0];
        $byrep = $result[1];

        $reps = [];
        $counts = [];
        $durations_secs = [];
        $durations_hms = [];

        foreach ($byrep as $rec) {
            $reps[] = $rec['Rep'];
            $counts[] = $rec['Count'];
            $durations_hms[] = secondsToHms($rec['Duration']);
            $durations_secs[] = $rec['Duration'];
        }

        $table_count = deleteColumn($bycamp, 'Duration');
        $table_duration = deleteColumn($bycamp, 'Count');

        $return = [
            'reps' => $reps,
            'counts' => $counts,
            'durations_secs' => $durations_secs,
            'durations_hms' => $durations_hms,
            'table_count' => $table_count,
            'table_duration' => $table_duration,
        ];

        echo json_encode($return);
    }

    public function serviceLevel(Request $request)
    {
        $this->getSession($request);

        $campaign = $this->campaign;
        $dateFilter = $this->dateFilter;
        $answerSecs = $request->answer_secs ?? 20;

        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $sql = "SELECT
         SUM([Handled]) as [Handled], 
         SUM([Count]) as [Count]
         FROM ( ";
        $union = '';
        foreach (Auth::user()->getDatabaseArray() as $i => $db) {

            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $startDate;
            $bind['todate' . $i] = $endDate;
            $bind['answersecs' . $i] = $answerSecs;

            $sql .= " $union SELECT 'Handled' = COUNT(CASE WHEN HoldTime < :answersecs$i AND CallStatus <> 'CR_HANGUP' THEN 1 ELSE NULL END),
            'Count' = COUNT(CallStatus)
            FROM [$db].[dbo].[DialingResults] DR
            WHERE CallType = 1
            AND CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound','TRANSFERRED','PARKED')
            AND DR.Date >= :fromdate$i
            AND DR.Date < :todate$i
            AND DR.GroupId = :groupid$i";

            if (!empty($campaign) && $campaign != 'Total') {
                $sql .= " AND DR.Campaign = :campaign$i";
                $bind['campaign' . $i] = $campaign;
            }

            $union = 'UNION ALL';
        }
        $sql .= ") tmp";

        $result = $this->runSql($sql, $bind);

        // now turn results into something usable
        $handled = $result[0]['Handled'];
        $count = $result[0]['Count'];

        if ($count > 0) {
            $pct = $handled / $count * 100;
        } else {
            $pct = 0;
        }

        $rem = 100 - $pct;
        $pct = round($pct);

        $return['service_level'] = ['service_level' => $pct, 'remainder' => $rem];
        echo json_encode($return);
    }

    public function repAvgHandleTime(Request $request)
    {
        $this->getSession($request);

        $campaign = $this->campaign;
        $dateFilter = $this->dateFilter;

        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $sql = "SET NOCOUNT ON;
        SELECT Rep, Campaign,
        'Duration' = SUM(Duration),
        'Count' = COUNT(CallStatus)
        INTO #temp
        FROM (";
        $union = '';
        foreach (Auth::user()->getDatabaseArray() as $i => $db) {

            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $startDate;
            $bind['todate' . $i] = $endDate;

            $sql .= " $union SELECT Rep, Campaign,
            Duration, CallStatus
            FROM [$db].[dbo].[DialingResults] DR
            WHERE CallType NOT IN (7,8)
            AND CallStatus NOT IN('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound','TRANSFERRED','PARKED')
            AND HoldTime >= 0
            AND Duration > 0
            AND DR.Date >= :fromdate$i
            AND DR.Date < :todate$i
            AND DR.GroupId = :groupid$i ";

            if (!empty($campaign) && $campaign != 'Total') {
                $sql .= " AND DR.Campaign = :campaign$i";
                $bind['campaign' . $i] = $campaign;
            }

            $union = 'UNION ALL';
        }

        $sql .= ") tmp
        GROUP BY Rep, Campaign;
        
        SELECT Rep, Campaign, 'AverageHandleTime' = [Duration]/[Count]
        FROM #temp
        ORDER BY Rep, Campaign;
        
        SELECT Rep,
        'AverageHandleTime' = SUM([Duration])/SUM([Count])
        FROM #temp
        GROUP BY Rep
        ORDER BY 'AverageHandleTime' DESC";

        $result = $this->runMultiSql($sql, $bind);

        $bycamp = $result[0];
        $byrep = $result[1];

        $reps = [];
        $handletime = [];
        $handletimesecs = [];
        foreach ($byrep as $rec) {
            $reps[] = $rec['Rep'];
            $handletimesecs[] = $rec['AverageHandleTime'];
            $handletime[] = secondsToHms($rec['AverageHandleTime']);
        }

        $return = [
            'reps' => $reps,
            'avg_handletime' => $handletime,
            'avg_handletimesecs' => $handletimesecs,
            'table' => $bycamp,
        ];

        echo json_encode($return);
    }
}
