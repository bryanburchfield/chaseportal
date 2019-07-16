<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Campaign;
use App\DialingResult;

class AdminDashController extends Controller
{
    use DashTraits;

    public function index(Request $request)
    {
        $this->getSession();

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
        $this->getSession();

        $campaign = $this->campaign;
        $dateFilter = $this->dateFilter;
        $timeZoneName = Auth::user()->tz;

        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        $byHour = ($dateFilter == 'today' || $dateFilter == 'yesterday') ? true : false;

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

        $select = "'Time' = $xAxis,
        'Inbound Count' = SUM(CASE WHEN CallType IN ('1','11') THEN 1 ELSE 0 END),
        'Inbound Handled Calls' = SUM(CASE WHEN CallType IN ('1','11') AND CallStatus NOT IN ( 'CR_CEPT', 'CR_CNCT/CON_PAMD', 'CR_NOANS', 'CR_NORB', 'CR_BUSY',
          'CR_DROPPED', 'CR_FAXTONE', 'CR_FAILED', 'CR_DISCONNECTED',
          'CR_HANGUP', 'Inbound Voicemail') THEN 1 ELSE 0 END),
        'Inbound Voicemails' = SUM(CASE WHEN CallType IN ('1','11') AND CallStatus='Inbound Voicemail' THEN 1 ELSE 0 END),
        'Inbound Abandoned Calls' = SUM(CASE WHEN CallType IN ('1','11') AND CallStatus='CR_HANGUP' THEN 1 ELSE 0 END),
        'Inbound Dropped Calls' = SUM(CASE WHEN CallType IN ('1','11') AND CallStatus='CR_DROPPED' THEN 1 ELSE 0 END),
        'Duration Inbound' = SUM(CASE WHEN CallType IN ('1','11') THEN Duration ELSE 0 END),
        'Outbound Count' = SUM(CASE WHEN CallType NOT IN ('1','11') THEN 1 ELSE 0 END),
        'Outbound Handled Calls' = SUM(CASE WHEN CallType NOT IN ('1','11') AND CallStatus NOT IN ( 'CR_CEPT', 'CR_CNCT/CON_PAMD', 'CR_NOANS', 'CR_NORB', 'CR_BUSY',
          'CR_DROPPED', 'CR_FAXTONE', 'CR_FAILED', 'CR_DISCONNECTED',
          'CR_HANGUP', 'Inbound Voicemail') THEN 1 ELSE 0 END),
        'Outbound Abandoned Calls' = SUM(CASE WHEN CallType NOT IN ('1','11') AND CallStatus='CR_HANGUP' THEN 1 ELSE 0 END),
        'Outbound Dropped Calls' = SUM(CASE WHEN CallType NOT IN ('1','11') AND CallStatus='CR_DROPPED' THEN 1 ELSE 0 END),
        'Duration Outbound' = SUM(CASE WHEN CallType NOT IN ('1','11') THEN Duration ELSE 0 END)";

        $query = DialingResult::select(DB::raw($select));

        $query->where('CallType', '!=', 7)
            ->whereNotIn('CallStatus', ['CR_CNCT/CON_CAD', 'CR_CNCT/CON_PVD', 'Inbound'])
            ->where('Duration', '>', 0)
            ->where('Date', '>=', $startDate)
            ->where('Date', '<', $endDate)
            ->where('GroupId', Auth::user()->group_id);

        if (!empty($campaign) && $campaign != 'Total') {
            $query->where('Campaign', $campaign);
        }

        $query->groupBy(DB::raw($xAxis));

        $result = $query->get()->toArray();

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
        $this->returnCallVolume(
            array_map(array(&$this, $mapFunction), $inResult),
            array_map(array(&$this, $mapFunction), $outResult),
            array_map(array(&$this, $mapFunction), $durResult)
        );
    }

    private function returnCallVolume($inbound, $outbound, $duration)
    {
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

        foreach ($inbound as $r) {

            if (!strpos($r['Time'], ':')) {
                $datetime = date("n/j/y", strtotime($r['Time']));
            } else {
                $datetime = $r['Time'];
            }

            array_push($inbound_time_labels, $datetime);
            array_push($total_inbound_calls, $r['Inbound Count']);
            array_push($inbound_voicemails, $r['Inbound Voicemails']);
            array_push($inbound_abandoned, $r['Inbound Abandoned Calls']);
            array_push($inbound_handled, $r['Inbound Handled Calls']);
        }

        foreach ($outbound as $r) {

            if (!strpos($r['Time'], ':')) {
                $datetime = date("n/j/y", strtotime($r['Time']));
            } else {
                $datetime = $r['Time'];
            }

            array_push($outbound_time_labels, $datetime);
            array_push($total_outbound_calls, $r['Outbound Count']);
            array_push($outbound_handled, $r['Outbound Handled Calls']);
            array_push($outbound_dropped, $r['Outbound Dropped Calls']);
        }

        foreach ($duration as $r) {

            if (!strpos($r['Time'], ':')) {
                $datetime = date("n/j/y", strtotime($r['Time']));
            } else {
                $datetime = $r['Time'];
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
        $new_result['total'] = $total;

        $return['call_volume'] = $new_result;
        echo json_encode($return);
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
        $this->getSession();

        $campaign = $this->campaign;
        $dateFilter = $this->dateFilter;

        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $select = "CallType AS 'CallType',
                    COUNT(CallStatus) AS 'Agent Calls'";

        $query = DialingResult::select(DB::raw($select));

        $query->where('CallType', '!=', 7)
            ->whereNotIn('CallStatus', ['CR_CEPT', 'CR_CNCT/CON_PAMD', 'CR_NOANS', 'CR_NORB', 'CR_BUSY', 'CR_DROPPED', 'CR_FAXTONE', 'CR_FAILED', 'CR_DISCONNECTED', 'CR_CNCT/CON_CAD', 'CR_CNCT/CON_PVD', ' ', 'CR_HANGUP', 'Inbound'])
            ->where('Date', '>=', $startDate)
            ->where('Date', '<', $endDate)
            ->where('GroupId', Auth::user()->group_id);

        if (!empty($campaign) && $campaign != 'Total') {
            $query->where('Campaign', $campaign);
        }

        $query->groupBy('CallType');

        $result = $query->get()->toArray();

        // now turn results into something usable
        $inbound = ['1', '11'];

        $total_completed_calls = 0;
        $outbound_completed_calls = 0;
        $inbound_completed_calls = 0;

        foreach ($result as $call) {
            $total_completed_calls += $call['Agent Calls'];
            if (in_array($call['CallType'], $inbound)) {
                $inbound_completed_calls += $call['Agent Calls'];
            } else {
                $outbound_completed_calls += $call['Agent Calls'];
            }
        }

        list($campaign, $details) = $this->filterDetails();

        $return['completed_calls'] = ['total' => $total_completed_calls, 'outbound' => $outbound_completed_calls, 'inbound' => $inbound_completed_calls, 'campaign' => $campaign, 'details' => $details];
        echo json_encode($return);
    }

    public function avgHoldTime(Request $request)
    {
        $this->getSession();

        $campaign = $this->campaign;
        $dateFilter = $this->dateFilter;

        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $select = "'Total Calls' = COUNT(CallStatus),
                'Hold Time' = CAST(DATEADD(SECOND, SUM(HoldTime), 0) AS TIME(0)),
                'Average Hold Time' = CAST(DATEADD(SECOND, (SUM(HoldTime) / COUNT(CallStatus)), 0) AS TIME(0))";

        $query = DialingResult::select(DB::raw($select));

        $query->where('CallType', 1)
            ->whereNotIn('CallStatus', ['CR_CNCT/CON_CAD', 'CR_CNCT/CON_PVD', 'Inbound', 'TRANSFERRED', 'PARKED'])
            ->where('Holdtime', '>=', 0)
            ->where('Date', '>=', $startDate)
            ->where('Date', '<', $endDate)
            ->where('GroupId', Auth::user()->group_id);

        if (!empty($campaign) && $campaign != 'Total') {
            $query->where('Campaign', $campaign);
        }

        $result = $query->get()->toArray();

        // now turn results into something usable
        if (!$result[0]['Total Calls']) {
            $avg_hold_time = '0:00';
        } else {
            $avg_hold_time = $result[0]['Average Hold Time'];
        }

        if (empty($result[0]['Hold Time'])) {
            $total_hold_time = '0:00';
        } else {
            $total_hold_time = $result[0]['Hold Time'];
        }

        $return['average_hold_time'] =  ['avg_hold_time' => $avg_hold_time, 'total_hold_time' => $total_hold_time];
        echo json_encode($return);
    }

    public function abandonRate(Request $request)
    {
        $this->getSession();

        $campaign = $this->campaign;
        $dateFilter = $this->dateFilter;

        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $select = "'Total Inbound Calls' = COUNT(CallStatus),
        'Abandoned Calls' = COUNT(CASE WHEN CallStatus='CR_HANGUP' THEN 1 ELSE NULL END)";

        $query = DialingResult::select(DB::raw($select));

        $query->where('CallType', 1)
            ->whereNotIn('CallStatus', ['CR_CNCT/CON_CAD', 'CR_CNCT/CON_PVD', 'Inbound', 'TRANSFERRED', 'PARKED'])
            ->where('Date', '>=', $startDate)
            ->where('Date', '<', $endDate)
            ->where('GroupId', Auth::user()->group_id);

        if (!empty($campaign) && $campaign != 'Total') {
            $query->where('Campaign', $campaign);
        }

        $result = $query->get()->toArray();

        // now turn results into something usable
        $abandon_calls = $result[0]['Abandoned Calls'];
        $total_calls = $result[0]['Total Inbound Calls'];

        if (empty($abandon_calls)) {
            $abandon_pct = '0.00%';
        } else {
            $abandon_pct = round(($abandon_calls / $total_calls) * 100, 2) . '%';
        }

        $return['abandon_rate'] = ['abandon_calls' => $abandon_calls, 'abandon_rate' => $abandon_pct];
        echo json_encode($return);
    }

    public function agentCallCount(Request $request)
    {
        $this->getSession();

        $campaign = $this->campaign;
        $dateFilter = $this->dateFilter;

        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $bind['groupid'] = Auth::user()->group_id;
        $bind['fromdate'] = $startDate;
        $bind['todate'] = $endDate;

        $sql = "SET NOCOUNT ON;
        
        SELECT Rep, Campaign,
        'Count' = SUM([Count]),
        'Duration' = SUM(Duration)
        INTO #temp
        FROM (";
        $union = '';
        foreach (Auth::user()->getDatabaseArray() as $db) {
            $sql .= " $union SELECT DR.Rep, DR.Campaign,
            'Count' = COUNT(DR.CallStatus),
            'Duration' = SUM(DR.Duration)
            FROM [$db].[dbo].[DialingResults] DR
            WHERE DR.CallType NOT IN (7,8)
            AND DR.CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound')
            AND Duration <> 0
            AND DR.Date >= :fromdate
            AND DR.Date < :todate
            AND DR.GroupId = :groupid ";

            if (!empty($campaign) && $campaign != 'Total') {
                $sql .= " AND DR.Campaign = :campaign";
                $bind['campaign'] = $campaign;
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

        $db = Auth::user()->db;
        config(['database.connections.sqlsrv.database' => $db]);

        $pdo = DB::connection('sqlsrv')->getPdo();
        $stmt = $pdo->prepare($sql, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);

        foreach ($bind as $k => $v) {
            $stmt->bindValue($k, $v);
        }

        $stmt->execute();

        try {
            $bycamp = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $stmt->nextRowset();
            $byrep = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $bycamp = [];
            $byrep = [];
        }

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
        $this->getSession();

        $campaign = $this->campaign;
        $dateFilter = $this->dateFilter;

        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $select = "'handled' = COUNT(CASE WHEN HoldTime < 20 AND CallStatus <> 'CR_HANGUP' THEN 1 ELSE NULL END),
         'count' = COUNT(CallStatus)";

        $query = DialingResult::select(DB::raw($select));

        $query->where('CallType', 1)
            ->whereNotIn('CallStatus', ['CR_CNCT/CON_CAD', 'CR_CNCT/CON_PVD', 'Inbound', 'TRANSFERRED', 'PARKED'])
            ->where('Date', '>=', $startDate)
            ->where('Date', '<', $endDate)
            ->where('GroupId', Auth::user()->group_id);

        if (!empty($campaign) && $campaign != 'Total') {
            $query->where('Campaign', $campaign);
        }

        // DB::connection('sqlsrv')->enableQueryLog();
        $result = $query->get()->toArray();
        // Log::debug(DB::connection('sqlsrv')->getQueryLog());

        // now turn results into something usable
        $handled = $result[0]['handled'];
        $count = $result[0]['count'];

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
        $this->getSession();

        $campaign = $this->campaign;
        $dateFilter = $this->dateFilter;

        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $bind['groupid'] = Auth::user()->group_id;
        $bind['fromdate'] = $startDate;
        $bind['todate'] = $endDate;

        $sql = "SET NOCOUNT ON;
        SELECT Rep, Campaign,
        'Duration' = SUM(Duration),
        'Count' = COUNT(CallStatus)
        INTO #temp
        FROM (";
        $union = '';
        foreach (Auth::user()->getDatabaseArray() as $db) {
            $sql .= " $union SELECT Rep, Campaign,
            Duration, CallStatus
            FROM [$db].[dbo].[DialingResults] DR
            WHERE CallType NOT IN (7,8)
            AND CallStatus NOT IN('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound','TRANSFERRED','PARKED')
            AND HoldTime >= 0
            AND Duration > 0
            AND DR.Date >= :fromdate
            AND DR.Date < :todate
            AND DR.GroupId = :groupid ";

            if (!empty($campaign) && $campaign != 'Total') {
                $sql .= " AND DR.Campaign = :campaign";
                $bind['campaign'] = $campaign;
            }

            $union = 'UNION ALL';
        }

        $sql .= ") tmp
        GROUP BY Rep, Campaign;
        
        SELECT Rep, Campaign, 'Average Handle Time' = [Duration]/[Count]
        FROM #temp
        ORDER BY Rep, Campaign;
        
        SELECT Rep,
        'Average Handle Time' = SUM([Duration])/SUM([Count])
        FROM #temp
        GROUP BY Rep
        ORDER BY 'Average Handle Time' DESC";

        $db = Auth::user()->db;
        config(['database.connections.sqlsrv.database' => $db]);

        $pdo = DB::connection('sqlsrv')->getPdo();
        $stmt = $pdo->prepare($sql, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);

        foreach ($bind as $k => $v) {
            $stmt->bindValue($k, $v);
        }

        $stmt->execute();

        try {
            $bycamp = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $stmt->nextRowset();
            $byrep = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $bycamp = [];
            $byrep = [];
        }

        $reps = [];
        $handletime = [];
        $handletimesecs = [];
        foreach ($byrep as $rec) {
            $reps[] = $rec['Rep'];
            $handletimesecs[] = $rec['Average Handle Time'];
            $handletime[] = secondsToHms($rec['Average Handle Time']);
        }

        $return = [
            'reps' => $reps,
            'avg_handletime' => $handletime,
            'avg_handletimesecs' => $handletimesecs,
            'table' => $bycamp,
        ];

        echo json_encode($return);
    }

    public function setCampaign(Request $request)
    {
        return 'set camp here';
    }
}
