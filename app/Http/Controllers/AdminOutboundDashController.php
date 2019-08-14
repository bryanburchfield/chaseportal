<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Campaign;
use \App\Traits\DashTraits;

class AdminOutboundDashController extends Controller
{
    use DashTraits;

    /**
     * return view
     *
     * @param Request $request
     * @return view
     */
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
            'datefilter' => $this->dateFilter,
            'inorout' => $this->inorout,
            'campaign_list' => $campaigns,
            'curdash' => 'adminoutbounddash',
            'jsfile' => $jsfile,
            'cssfile' => $cssfile,
        ];
        return view('adminoutbounddash')->with($data);
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
        $prev_result = $this->getCallVolume(true);

        // cards to be populated
        $call_volume = [
            'time_labels' => [],
            'total_calls' => [],
            'handled' => [],
            'dropped' => [],
        ];
        $call_duration = [
            'time_labels' => [],
            'duration' => [],
        ];
        $total_duration = [
            'duration' => 0,
            'pct_change' => 0,
            'pct_sign' => 0,
            'ntc' => 0,
        ];

        // Prev tots for rate change calcs
        $prev_total_duration = 0;

        foreach ($result[0] as $r) {
            if ($this->byHour($this->dateFilter)) {
                $datetime = date("g:i", strtotime($r['Time']));
            } else {
                $datetime = date("n/j/y", strtotime($r['Time']));
            }

            array_push($call_volume['time_labels'], $datetime);
            array_push($call_volume['total_calls'], $r['Count']);
            array_push($call_volume['handled'], $r['Handled Calls']);
            array_push($call_volume['dropped'], $r['Dropped Calls']);
        }

        foreach ($result[1] as $r) {
            if ($this->byHour($this->dateFilter)) {
                $datetime = date("g:i", strtotime($r['Time']));
            } else {
                $datetime = date("n/j/y", strtotime($r['Time']));
            }

            array_push($call_duration['time_labels'], $datetime);
            array_push($call_duration['duration'], $r['Duration']);

            $total_duration['duration'] += $r['Duration'];
        }

        foreach ($prev_result[1] as $r) {
            $prev_total_duration += $r['Duration'];
        }

        if ($prev_total_duration == 0) {
            $total_duration['pct_change'] = null;
            $total_duration['pct_sign'] = null;
            $total_duration['ntc'] = 1;  // nothing to compare
        } else {
            $total_duration['pct_change'] = ($total_duration['duration'] - $prev_total_duration) / $prev_total_duration * 100;
            $total_duration['pct_sign'] = $total_duration['pct_change'] < 0 ? 0 : 1;
            $total_duration['pct_change'] = round(abs($total_duration['pct_change']));
            $ntc = 0;
        }

        return [
            'call_volume' => [
                'call_volume' => $call_volume,
                'call_duration' => $call_duration,
                'total_duration' => $total_duration,
            ],
        ];
    }

    /**
     * query call volume
     *
     * @param boolean $prev
     * @return array
     */
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
        SUM([Count]) AS 'Count',
        SUM([Handled Calls]) AS 'Handled Calls',
        SUM([Abandoned Calls]) AS 'Abandoned Calls',
        SUM([Dropped Calls]) AS 'Dropped Calls',
        SUM([Duration]) AS 'Duration'
        FROM (";

        $union = '';
        foreach (Auth::user()->getDatabaseArray() as $i => $db) {
            $sql .= " $union SELECT $xAxis as 'Time',
    'Count' = SUM(1),
    'Handled Calls' = SUM(CASE WHEN DR.CallStatus NOT IN ( 'CR_CEPT', 'CR_CNCT/CON_PAMD', 'CR_NOANS', 'CR_NORB', 'CR_BUSY',
    'CR_DROPPED', 'CR_FAXTONE', 'CR_FAILED', 'CR_DISCONNECTED',
    'CR_HANGUP', 'Inbound Voicemail') THEN 1 ELSE 0 END),
    'Abandoned Calls' = SUM(CASE WHEN DR.CallStatus='CR_HANGUP' THEN 1 ELSE 0 END),
    'Dropped Calls' = SUM(CASE WHEN DR.CallStatus='CR_DROPPED' THEN 1 ELSE 0 END),
    'Duration' = SUM(DR.Duration)
            FROM [$db].[dbo].[DialingResults] DR
            WHERE DR.CallType NOT IN (1,7,8,11)
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

        $outResult = $this->outboundVolume($result, $params);
        $durResult = $this->callDuration($result, $params);

        // now format the xAxis datetimes and return the results
        return [
            array_map(array(&$this, $mapFunction), $outResult),
            array_map(array(&$this, $mapFunction), $durResult),
        ];
    }

    /**
     * return outbound volume
     *
     * @param array $result
     * @param array $params
     * @return array
     */
    protected function outboundVolume($result, $params)
    {
        // define recs with no data to compare against or insert if we need to fill in gaps
        $zeroRec = [
            'Time' => '',
            'Count' => 0,
            'Handled Calls' => 0,
            'Abandoned Calls' => 0,
            'Dropped Calls' => 0,
        ];

        return ($this->zeroRecs($result, $zeroRec, $params));
    }

    /**
     * return call duration
     *
     * @param array $result
     * @param array $params
     * @return array
     */
    protected function callDuration($result, $params)
    {
        // extract Time and Duration fields from array
        $duration = [];
        foreach ($result as $rec) {
            foreach ($rec as $k => $v) {
                if ($k != 'Time' && $k != 'Duration') {
                    unset($rec[$k]);
                }
            }
            $duration[] = $rec;
        }

        // define recs with no data to compare against or insert if we need to fill in gaps
        $zeroRec = [
            'Time' => '',
            'Duration' => 0,
        ];

        return ($this->zeroRecs($duration, $zeroRec, $params));
    }

    /**
     * return agent talk time
     *
     * @param Request $request
     * @return void
     */
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

        return [
            'reps' => $reps,
            'counts' => $counts,
            'durations_secs' => $durations_secs,
            'durations_hms' => $durations_hms,
            'table_count' => $table_count,
            'table_duration' => $table_duration,
        ];
    }

    /**
     * return calls by campaign
     *
     * @param Request $request
     * @return void
     */
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

        $result = $this->runSql($sql, $bind);

        $camps = array_column($result, 'Campaign');
        $counts = array_column($result, 'CallCount');

        return [
            'Table' => $result,
            'Campaigns' => $camps,
            'Counts' => $counts,
        ];
    }

    /**
     * return sales per hour per rep
     *
     * @param Request $request
     * @return void
     */
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

        return [
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
    }

    /**
     * query sales per hour per rep
     *
     * @param boolean $prev
     * @return array
     */
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

    /**
     * return average wait time
     *
     * @param Request $request
     * @return void
     */
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

        return ['avg_wait_time' => $result];
    }

    /**
     * return total calls
     *
     * @param Request $request
     * @return void
     */
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

        return [
            'total_calls' => [
                'total' => $total_total_calls,
                'outbound' => $outbound_total_calls,
                'inbound' => $inbound_total_calls,
                'details' => $details,
                'pct_change' => $pctdiff,
                'pct_sign' => $pctsign,
                'ntc' => $ntc,
            ],
        ];
    }

    /**
     * query total calls
     *
     * @param boolean $prev
     * @return array
     */
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
