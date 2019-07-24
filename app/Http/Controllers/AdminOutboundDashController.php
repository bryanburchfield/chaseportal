<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Campaign;

class AdminOutboundDashController extends Controller
{
    use DashTraits;

    public function index(Request $request)
    {
        $this->getSession($request);

        $groupId = Auth::user()->group_id;
        $campaigns = Campaign::where('GroupId', $groupId)->where('IsActive', 1)->pluck('CampaignName')->toArray();
        natcasesort($campaigns);
        array_unshift($campaigns, 'Total');

        $jsfile[] = "adminoutbounddash.js";
        $cssfile[] = "adminoutbounddash.css";

        $data = [
            'isApi' => $this->isApi,
            'campaign' => $this->campaign,
            'dateFilter' => $this->dateFilter,
            'inorout' => $this->inorout,
            'campaign_list' => $campaigns,
            'curdash' => 'adminoutbounddash',
            'jsfile' => $jsfile,
            'cssfile' => $cssfile,
        ];
        return view('adminoutbounddash')->with($data);
    }

    public function callVolume(Request $request)
    {
        $this->getSession($request);

        $call_volume = $this->getCallVolume();
        $prev_call_volume = $this->getCallVolume(true);

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
        $prev_total_duration = 0;

        foreach ($call_volume[0] as $r) {

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

        foreach ($call_volume[1] as $r) {

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

        foreach ($call_volume[2] as $r) {
            $total_inbound_duration += $r['Duration Inbound'];
            $total_outbound_duration += $r['Duration Outbound'];

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
        }

        foreach ($prev_call_volume[2] as $r) {
            $prev_total_duration += $r['Duration Inbound'] + $r['Duration Outbound'];
        }

        $total_duration = $total_inbound_duration + $total_outbound_duration;

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

        $total_inbound_duration = round($total_inbound_duration / 60);
        $total_outbound_duration = round($total_outbound_duration / 60);

        $total = $total_inbound_duration + $total_outbound_duration;

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
            'outbound_handled' => $outbound_handled,
            'outbound_duration' => $outbound_duration,
            'total_inbound_duration' => $total_inbound_duration,
            'total_outbound_duration' => $total_outbound_duration,
            'duration_time' => $duration_time,
            'total' => $total,
            'pct_change' => $pctdiff,
            'pct_sign' => $pctsign,
            'ntc' => $ntc,
        ];

        $return['call_volume'] = $new_result;
        echo json_encode($return);
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
            'fromdate' => $startDate,
            'todate' => $endDate,
            'groupid' =>  Auth::user()->group_id,
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

    protected function inboundVolume($result, $params)
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

    protected function outboundVolume($result, $params)
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

    protected function callDuration($result, $params)
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

    public function agentTalkTime(Request $request)
    {
        $this->getSession($request);

        $campaign = $this->campaign;
        $dateFilter = $this->dateFilter;

        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $bind = [
            'fromdate' => $startDate,
            'todate' => $endDate,
            'groupid' => Auth::user()->group_id,
        ];

        $sql = "SET NOCOUNT ON;
        
        SELECT Rep, Campaign,
        'Count' = SUM([Count]),
        'Duration' = SUM(Duration)
        INTO #temp
        FROM (";
        $union = '';
        foreach (Auth::user()->getDatabaseArray() as $i => $db) {
            $sql .= " $union SELECT DR.Rep, DR.Campaign,
            'Count' = COUNT(DR.CallStatus),
            'Duration' = SUM(DR.Duration)
            FROM [$db].[dbo].[DialingResults] DR
            WHERE DR.CallType NOT IN (1,7,8,11)
            AND DR.CallStatus NOT IN (
                'CR_CEPT', 'CR_CNCT/CON_PAMD', 'CR_NOANS',
                'CR_NORB', 'CR_BUSY', 'CR_DROPPED', 'CR_FAXTONE',
                'CR_FAILED', 'CR_DISCONNECTED', 'CR_CNCT/CON_CAD',
                'CR_CNCT/CON_PVD', ' ', 'CR_HANGUP', 'Inbound')
            AND Duration <> 0
            AND DR.Date >= :fromdate
            AND DR.Date < :todate
            AND DR.GroupId = :groupid";

            if (!empty($campaign) && $campaign != 'Total') {
                $sql .= " AND DR.Campaign = :campaign$i";
                $bind['campaign' . $i] = $campaign;
            }

            $sql .= "
            GROUP BY DR.Rep, DR.Campaign";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp
        GROUP BY Rep, Campaign;
        
        SELECT Rep, Campaign, SUM([Count]) as [Count], SUM(Duration) as Duration
        FROM #temp
        GROUP BY Rep, Campaign
        ORDER BY Rep, Campaign;
        
        SELECT Rep, SUM([Count]) as [Count], SUM(Duration) as Duration
        FROM #temp
        GROUP BY Rep
        ORDER BY Rep";

        list($bycamp, $byrep) = $this->runMultiSql($sql, $bind);

        $reps = [];
        $counts = [];
        $durations_secs = [];
        $durations_hms = [];

        foreach ($byrep as &$rec) {

            $rec['AvgDurationSecs'] = $rec['Duration'] / $rec['Count'];
            $rec['AvgCallsPerHour'] = round($rec['Count'] / ($rec['Duration'] / 60 / 60), 2);

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

    public function callsByCampaign(Request $request)
    {
        $this->getSession($request);

        $campaign = $this->campaign;
        $dateFilter = $this->dateFilter;

        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $bind = [
            'groupid1' => Auth::user()->group_id,
            'groupid2' => Auth::user()->group_id,
            'fromdate1' => $startDate,
            'fromdate2' => $startDate,
            'todate1' => $endDate,
            'todate2' => $endDate,
        ];

        $sql = "SELECT Campaign,
		'CallCount' = SUM(Cnt)
		FROM (";
        $union = '';
        foreach (Auth::user()->getDatabaseArray() as $i => $db) {
            $sql .= " $union SELECT DR.Campaign,
			'Cnt' = COUNT(DR.CallStatus)
			FROM [$db].[dbo].[DialingResults] DR
			CROSS APPLY (SELECT TOP 1 [Type]
				FROM  [$db].[dbo].[Dispos]
				WHERE Disposition = DR.CallStatus
				AND (GroupId = DR.GroupId OR IsSystem=1)
				AND (Campaign = DR.Campaign OR Campaign = '') 
				ORDER BY [Description] Desc) DI
			WHERE DR.GroupId = :groupid1
			AND DR.Rep != ''
			AND DR.Date >= :fromdate1
            AND DR.Date < :todate1";

            if (!empty($campaign) && $campaign != 'Total') {
                $sql .= " AND DR.Campaign = :campaign$i";
                $bind['campaign' . $i] = $campaign;
            }

            $sql .= "
            GROUP BY DR.Campaign
          UNION ALL
            SELECT DR.Campaign,
			'Cnt' = COUNT(DR.CallStatus)
			FROM [$db].[dbo].[DialingResults] DR
			WHERE DR.GroupId = :groupid2
			AND DR.Rep = ''
			AND CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound','TRANSFERRED','PARKED')
			AND DR.Date >= :fromdate2
            AND DR.Date < :todate2";

            if (!empty($campaign) && $campaign != 'Total') {
                $sql .= " AND DR.Campaign = :campaign$i";
                $bind['campaign' . $i] = $campaign;
            }

            $sql .= "
                GROUP BY DR.Campaign";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp
		GROUP BY Campaign
		ORDER BY SUM(Cnt) DESC";

        Log::debug($sql);
        Log::debug($bind);
        $result = $this->runSql($sql, $bind);
        Log::debug($result);

        $camps = array_column($result, 'Campaign');
        $counts = array_column($result, 'CallCount');

        $return = [
            'Table' => $result,
            'Campaigns' => $camps,
            'Counts' => $counts,
        ];

        echo json_encode($return);
    }

    public function salesPerHourPerRep(Request $request)
    {
        $this->getSession($request);

        list($bycamp, $byrep) = $this->getSalesPerHourPerRep();
        list($prev_bycamp, $prev_byrep) = $this->getSalesPerHourPerRep(true);

        $reps = array_column($byrep, 'Rep');
        $sales = array_column($byrep, 'Sales');
        $bycamp = deleteColumn($bycamp, 'Talk Secs');

        $tots = [
            'Rep' => 'TOTAL',
            'Talk Secs' => 0,
            'Sales' => 0,
            'PerHour' => 0,
        ];

        $prev_tot_sales = 0;
        $prev_tot_secs = 0;

        foreach ($byrep as &$rec) {
            $tots['Talk Secs'] += $rec['Talk Secs'];
            $tots['Sales'] += $rec['Sales'];

            $rec['PerHour'] = $rec['Talk Secs'] != 0 ? round($rec['Sales'] / $rec['Talk Secs'] * 3600, 2) : 0;
            $rec['Talk Secs'] = secondsToHms($rec['Talk Secs']);
        }
        $tots['PerHour'] = $tots['Talk Secs'] != 0 ? round($tots['Sales'] / $tots['Talk Secs'] * 3600, 2) : 0;
        $tots['Talk Secs'] = secondsToHms($tots['Talk Secs']);

        $byrep[] = $tots;

        foreach ($prev_byrep as &$rec) {
            $prev_tot_secs += $rec['Talk Secs'];
            $prev_tot_sales += $rec['Sales'];
        }

        if ($prev_tot_sales == 0) {
            $sales_pctdiff = null;
            $sales_pctsign = null;
            $sales_ntc = 1;  // nothing to compare
        } else {
            $sales_pctdiff = ($tots['Sales'] - $prev_tot_sales) / $prev_tot_sales * 100;
            $sales_pctsign = $sales_pctdiff < 0 ? 0 : 1;
            $sales_pctdiff = round(abs($sales_pctdiff));
            $sales_ntc = 0;
        }

        $prev_per_hour = $prev_tot_secs != 0 ? round($prev_tot_sales / $prev_tot_secs * 3600, 2) : 0;

        if ($prev_per_hour == 0) {
            $perhour_pctdiff = null;
            $perhour_pctsign = null;
            $perhour_ntc = 1;  // nothing to compare
        } else {
            $perhour_pctdiff = ($tots['PerHour'] - $prev_per_hour) / $prev_per_hour * 100;
            $perhour_pctsign = $perhour_pctdiff < 0 ? 0 : 1;
            $perhour_pctdiff = round(abs($perhour_pctdiff));
            $perhour_ntc = 0;
        }

        $return = [
            'table' => $bycamp,
            'total_sales' => $tots['Sales'],
            'total_sales_per_hour' => $tots['PerHour'],
            'reps' => $reps,
            'sales' => $sales,
            'sales_pct_change' => $sales_pctdiff,
            'sales_pct_sign' => $sales_pctsign,
            'sales_ntc' => $sales_ntc,
            'perhour_pct_change' => $perhour_pctdiff,
            'perhour_pct_sign' => $perhour_pctsign,
            'perhour_ntc' => $perhour_ntc,
        ];

        echo json_encode($return);
    }

    public function getSalesPerHourPerRep($prev = false)
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

        $bind = [
            'groupid' => Auth::user()->group_id,
            'fromdate' => $startDate,
            'todate' => $endDate,
        ];

        $sql = "SET NOCOUNT ON;

        SELECT Rep, Campaign,
        'Duration' = SUM(Duration),
        'Sales' = SUM(Sales)
        INTO #temp
        FROM (";

        $union = '';
        foreach (Auth::user()->getDatabaseArray() as $i => $db) {
            $sql .= " $union SELECT
            DR.Rep, DR.Campaign,
            'Duration' = SUM(DR.Duration),
            'Sales' = COUNT(CASE WHEN DI.Type = '3' THEN 1 ELSE NULL END)
            FROM [$db].[dbo].[DialingResults] DR
            CROSS APPLY (SELECT TOP 1 [Type]
                FROM  [$db].[dbo].[Dispos]
                WHERE Disposition = DR.CallStatus
                AND (GroupId = DR.GroupId OR IsSystem=1)
                AND (Campaign = DR.Campaign OR Campaign = '') 
                ORDER BY [Description] Desc) DI
            WHERE DR.GroupId = :groupid
            AND DR.Rep != ''
            AND DR.CallType NOT IN (1,7,8,11)
            AND Duration <> 0
            AND DR.CallStatus NOT IN (
                'CR_CEPT', 'CR_CNCT/CON_PAMD', 'CR_NOANS',
                'CR_NORB', 'CR_BUSY', 'CR_DROPPED', 'CR_FAXTONE',
                'CR_FAILED', 'CR_DISCONNECTED', 'CR_CNCT/CON_CAD',
                'CR_CNCT/CON_PVD', ' ', 'CR_HANGUP', 'Inbound')
            AND DR.Date >= :fromdate
            AND DR.Date < :todate";

            if (!empty($campaign) && $campaign != 'Total') {
                $sql .= " AND DR.Campaign = :campaign$i";
                $bind['campaign' . $i] = $campaign;
            }

            $sql .= "
                GROUP BY DR.Rep, DR.Campaign";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp
        GROUP BY Rep, Campaign;
        
        SELECT Rep, Campaign, SUM(Sales) as Sales, SUM(Duration) as [Talk Secs]
        FROM #temp
        GROUP BY Rep, Campaign
        ORDER BY Rep, Campaign;
        
        SELECT Rep, SUM(Sales) as Sales, SUM(Duration) as [Talk Secs]
        FROM #temp
        GROUP BY Rep
        ORDER BY Rep";

        list($bycamp, $byrep) = $this->runMultiSql($sql, $bind);

        return [$bycamp, $byrep];
    }

    public function avgWaitTime(Request $request)
    {
        $this->getSession($request);

        $campaign = $this->campaign;
        $dateFilter = $this->dateFilter;

        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $bind = [
            'fromdate' => $startDate,
            'todate' => $endDate,
            'groupid' => Auth::user()->group_id,
        ];

        $sql = 'SELECT Rep, Campaign, SUM(Duration)/SUM(Cnt) as AvgWaitTime FROM (';
        $union = '';
        foreach (Auth::user()->getDatabaseArray() as $i => $db) {
            $sql .= " $union SELECT Rep, Campaign, SUM(Duration) as Duration, COUNT(AA.id) as Cnt
                FROM [$db].[dbo].[AgentActivity] AA
                WHERE [Action] = 'Waiting'
                AND AA.Duration > 0
                AND AA.Date >= :fromdate
                AND AA.Date < :todate
                AND AA.GroupId = :groupid";

            if (!empty($campaign) && $campaign != 'Total') {
                $sql .= " AND DR.Campaign = :campaign$i";
                $bind['campaign' . $i] = $campaign;
            }

            $sql .= "
                GROUP BY AA.Rep, AA.Campaign";

            $union = 'UNION ALL';
        }
        $sql .= ") tmp GROUP BY Rep, Campaign
            ORDER BY Rep, Campaign";

        $result = $this->runSql($sql, $bind);

        foreach ($result as &$rec) {
            $rec['AvgWaitTime'] = round($rec['AvgWaitTime']);
        }

        $return['avg_wait_time'] = $result;
        echo json_encode($return);
    }

    public function totalCalls(Request $request)
    {
        $this->getSession($request);

        $total_calls = $this->getTotalCalls();
        $prev_total_calls = $this->getTotalCalls(true);

        $inbound = ['1', '11'];

        $total_total_calls = 0;
        $prev_total_total_calls = 0;
        $outbound_total_calls = 0;
        $inbound_total_calls = 0;

        foreach ($total_calls as $call) {
            $total_total_calls += $call['Agent Calls'];
            if (in_array($call['CallType'], $inbound)) {
                $inbound_total_calls += $call['Agent Calls'];
            } else {
                $outbound_total_calls += $call['Agent Calls'];
            }
        }

        foreach ($prev_total_calls as $call) {
            $prev_total_total_calls += $call['Agent Calls'];
        }

        if ($prev_total_total_calls == 0) {
            $pctdiff = null;
            $pctsign = null;
            $ntc = 1;  // nothing to compare
        } else {
            $pctdiff = ($total_total_calls - $prev_total_total_calls) / $prev_total_total_calls * 100;
            $pctsign = $pctdiff < 0 ? 0 : 1;
            $pctdiff = round(abs($pctdiff));
            $ntc = 0;
        }

        $details = $this->filterDetails($this->dateFilter, $this->campaign);

        $return['total_calls'] = [
            'total' => $total_total_calls,
            'outbound' => $outbound_total_calls,
            'inbound' => $inbound_total_calls,
            'details' => $details,
            'pct_change' => $pctdiff,
            'pct_sign' => $pctsign,
            'ntc' => $ntc,
        ];

        echo json_encode($return);
    }

    public function getTotalCalls($prev = false)
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

        $bind = [
            'fromdate' => $startDate,
            'todate' => $endDate,
            'groupid' => Auth::user()->group_id,
        ];

        $sql = 'SELECT CallType, SUM([Agent Calls]) as [Agent Calls] FROM (';
        $union = '';
        foreach (Auth::user()->getDatabaseArray() as $i => $db) {
            $sql .= " $union SELECT DR.CallType AS 'CallType',
                COUNT(DR.CallStatus) AS 'Agent Calls'
                FROM [$db].[dbo].[DialingResults] DR
                WHERE DR.CallType NOT IN (1,7,8,11)
                AND DR.CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','TRANSFERRED','PARKED','Inbound')
                AND DR.Date >= :fromdate
                AND DR.Date < :todate
                AND DR.GroupId = :groupid";

            if (!empty($campaign) && $campaign != 'Total') {
                $sql .= " AND DR.Campaign = :campaign$i";
                $bind['campaign' . $i] = $campaign;
            }

            $sql .= "
                GROUP BY DR.CallType";

            $union = 'UNION ALL';
        }
        $sql .= ") tmp GROUP BY CallType";

        return $this->runSql($sql, $bind);
    }
}
