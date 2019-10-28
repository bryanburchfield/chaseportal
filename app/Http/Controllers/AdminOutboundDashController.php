<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $campaigns = $this->campaignGroups();

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
                $datetime = date("D n/j/y", strtotime($r['Time']));
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
                $datetime = date("D n/j/y", strtotime($r['Time']));
            }

            array_push($call_duration['time_labels'], $datetime);
            array_push($call_duration['duration'], $r['Duration']);

            $total_duration['duration'] += $r['Duration'];
        }

        return ['call_volume' => [
            'call_volume' => $call_volume,
            'call_duration' => $call_duration,
            'total_duration' => $total_duration,
        ]];
    }

    /**
     * query call volume
     *
     * @param boolean $prev
     * @return array
     */
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
        SUM([Count]) AS 'Count',
        SUM([Handled Calls]) AS 'Handled Calls',
        SUM([Abandoned Calls]) AS 'Abandoned Calls',
        SUM([Dropped Calls]) AS 'Dropped Calls',
        SUM([Duration]) AS 'Duration'
        FROM (";

        $union = '';
        foreach ($this->databases as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $startDate;
            $bind['todate' . $i] = $endDate;

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

        $bind = [];

        $sql = "SET NOCOUNT ON;

        SELECT Rep, Campaign,
        'Count' = SUM([Count]),
        'Duration' = SUM(Duration)
        INTO #temp
        FROM (";
        $union = '';
        foreach ($this->databases as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $startDate;
            $bind['todate' . $i] = $endDate;

            $sql .= " $union SELECT DR.Rep, DR.Campaign,
            'Count' = COUNT(DR.CallStatus),
            'Duration' = SUM(DR.Duration)
            FROM [$db].[dbo].[DialingResults] DR
            WITH (INDEX(IX_GroupDateDurationStatusType))
            WHERE DR.CallType NOT IN (1,7,8,11)
            AND DR.CallStatus NOT IN (
                'CR_CEPT', 'CR_CNCT/CON_PAMD', 'CR_NOANS',
                'CR_NORB', 'CR_BUSY', 'CR_DROPPED', 'CR_FAXTONE',
                'CR_FAILED', 'CR_DISCONNECTED', 'CR_CNCT/CON_CAD',
                'CR_CNCT/CON_PVD', ' ', 'CR_HANGUP', 'Inbound')
            AND DR.Duration <> 0
            AND DR.Rep <> ''
            AND DR.Date >= :fromdate$i
            AND DR.Date < :todate$i
            AND DR.GroupId = :groupid$i";

            list($where, $extrabind) = $this->campaignClause('DR', $i, $campaign);
            $sql .= " $where";
            $bind = array_merge($bind, $extrabind);

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

        $call_count_table = deleteColumn($bycamp, 'Duration');
        $talk_time_table = deleteColumn($bycamp, 'Count');

        // sort arrays
        usort($call_count_table, function ($a, $b) {
            return $b['Count'] <=> $a['Count'];
        });
        usort($talk_time_table, function ($a, $b) {
            return $b['Duration'] <=> $a['Duration'];
        });

        // take top 10
        $call_count_table = array_slice($call_count_table, 0, 10);
        $talk_time_table = array_slice($talk_time_table, 0, 10);

        // Sort byrep array by Counts first
        usort($byrep, function ($a, $b) {
            return $b['Count'] <=> $a['Count'];
        });
        $call_count_reps = array_column(array_slice($byrep, 0, 10), 'Rep');
        $call_count_counts = array_column(array_slice($byrep, 0, 10), 'Count');

        // Now Sort byrep array by Duration
        usort($byrep, function ($a, $b) {
            return $b['Duration'] <=> $a['Duration'];
        });
        $talk_time_reps = array_column(array_slice($byrep, 0, 10), 'Rep');
        $talk_time_secs = array_column(array_slice($byrep, 0, 10), 'Duration');

        $talk_time_hms = [];
        foreach ($talk_time_secs as $d) {
            $talk_time_hms[] = $this->secondsToHms($d);
        }

        return [
            'call_count_table' => $call_count_table,
            'call_count_reps' => $call_count_reps,
            'call_count_counts' => $call_count_counts,
            'talk_time_table' => $talk_time_table,
            'talk_time_reps' => $talk_time_reps,
            'talk_time_secs' => $talk_time_secs,
            'talk_time_hms' => $talk_time_hms,
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

        $bind = [];

        $sql = "SELECT TOP 10
        Campaign,
		'CallCount' = SUM(Cnt)
		FROM (";
        $union = '';
        foreach ($this->databases as $i => $db) {
            $bind['groupid1_' . $i] = Auth::user()->group_id;
            $bind['fromdate1_' . $i] = $startDate;
            $bind['todate1_' . $i] = $endDate;
            $bind['groupid2_' . $i] = Auth::user()->group_id;
            $bind['fromdate2_' . $i] = $startDate;
            $bind['todate2_' . $i] = $endDate;

            $sql .= " $union SELECT DR.Campaign,
			'Cnt' = COUNT(DR.CallStatus)
			FROM [$db].[dbo].[DialingResults] DR
			CROSS APPLY (SELECT TOP 1 [Type]
				FROM  [$db].[dbo].[Dispos]
				WHERE Disposition = DR.CallStatus
				AND (GroupId = DR.GroupId OR IsSystem=1)
				AND (Campaign = DR.Campaign OR Campaign = '')
				ORDER BY [Description] Desc) DI
			WHERE DR.GroupId = :groupid1_$i
			AND DR.Rep != ''
			AND DR.Date >= :fromdate1_$i
            AND DR.Date < :todate1_$i";

            list($where, $extrabind) = $this->campaignClause('DR', $i, $campaign);
            $sql .= " $where";
            $bind = array_merge($bind, $extrabind);

            $sql .= "
            GROUP BY DR.Campaign
          UNION ALL
            SELECT DR.Campaign,
			'Cnt' = COUNT(DR.CallStatus)
			FROM [$db].[dbo].[DialingResults] DR
			WHERE DR.GroupId = :groupid2_$i
			AND DR.Rep = ''
			AND CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound','TRANSFERRED','PARKED')
			AND DR.Date >= :fromdate2_$i
            AND DR.Date < :todate2_$i";

            list($where, $extrabind) = $this->campaignClause('DR', $i + 999, $campaign);
            $sql .= " $where";
            $bind = array_merge($bind, $extrabind);

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

        // Check for no return, set to zeros if so
        if (!count($bycamp)) {
            $bycamp = [[
                'Rep' => '',
                'Campaign' => '',
                'Contacts' => 0,
                'Sales' => 0,
                'Talk Secs' => 0,
            ]];
        }
        if (!count($byrep)) {
            $byrep = [[
                'Rep' => '',
                'Contacts' => 0,
                'Sales' => 0,
                'Talk Secs' => 0,
            ]];
        }
        if (!count($prev_byrep)) {
            $prev_byrep = [[
                'Rep' => '',
                'Contacts' => 0,
                'Sales' => 0,
                'Talk Secs' => 0,
            ]];
        }

        $tots = [
            'Rep' => 'TOTAL',
            'Talk Secs' => 0,
            'Contacts' => 0,
            'Prev Contacts' => 0,
            'Sales' => 0,
            'PerHour' => 0,
        ];

        $prev_tot_sales = 0;
        $prev_tot_secs = 0;

        foreach ($byrep as &$rec) {
            $tots['Talk Secs'] += $rec['Talk Secs'];
            $tots['Contacts'] += $rec['Contacts'];
            $tots['Sales'] += $rec['Sales'];

            $rec['PerHour'] = $rec['Talk Secs'] != 0 ? round($rec['Sales'] / $rec['Talk Secs'] * 3600, 2) : 0;
            $rec['Talk Secs'] = $this->secondsToHms($rec['Talk Secs']);
        }
        $tots['PerHour'] = $tots['Talk Secs'] != 0 ? round($tots['Sales'] / $tots['Talk Secs'] * 3600, 2) : 0;
        $tots['Talk Secs'] = $this->secondsToHms($tots['Talk Secs']);

        $byrep[] = $tots;

        foreach ($prev_byrep as &$rec) {
            $prev_tot_secs += $rec['Talk Secs'];
            $prev_tot_sales += $rec['Sales'];
            $tots['Prev Contacts'] += $rec['Contacts'];
        }

        if ($prev_tot_sales == 0) {
            $pctdiff = null;
            $pctsign = null;
            $ntc = 1;  // nothing to compare
        } else {
            $pctdiff = ($tots['Sales'] - $prev_tot_sales) / $prev_tot_sales * 100;
            $pctsign = $pctdiff < 0 ? 0 : 1;
            $pctdiff = round(abs($pctdiff));
            $ntc = 0;
        }

        $total_sales = [
            'total' => $tots['Sales'],
            'conversion_rate' => $tots['Contacts'] > 0 ? round($tots['Sales'] / $tots['Contacts'] * 100, 2) : 0,
            'pct_change' => $pctdiff,
            'pct_sign' => $pctsign,
            'ntc' => $ntc,
        ];

        $prev_per_hour = $prev_tot_secs != 0 ? round($prev_tot_sales / $prev_tot_secs * 3600, 2) : 0;

        if ($prev_per_hour == 0) {
            $pctdiff = null;
            $pctsign = null;
            $ntc = 1;  // nothing to compare
        } else {
            $pctdiff = ($tots['PerHour'] - $prev_per_hour) / $prev_per_hour * 100;
            $pctsign = $pctdiff < 0 ? 0 : 1;
            $pctdiff = round(abs($pctdiff));
            $ntc = 0;
        }

        $sales_per_hour = [
            'total' => $tots['PerHour'],
            'pct_change' => $pctdiff,
            'pct_sign' => $pctsign,
            'ntc' => $ntc,
        ];

        return [
            'table' => $bycamp,
            'reps' => $reps,
            'sales' => $sales,
            'total_sales' => $total_sales,
            'sales_per_hour' => $sales_per_hour,
        ];
    }

    /**
     * query sales per hour per rep
     *
     * @param boolean $prev
     * @return array
     */
    private function getSalesPerHourPerRep($prev = false)
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

        $bind = [];

        $sql = "SET NOCOUNT ON;

        SELECT Rep, Campaign,
        'Duration' = SUM(Duration),
        'Contacts' = SUM(Contacts),
        'Sales' = SUM(Sales)
        INTO #temp
        FROM (";

        $union = '';
        foreach ($this->databases as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $startDate;
            $bind['todate' . $i] = $endDate;

            $sql .= " $union SELECT
            DR.Rep, DR.Campaign,
            'Duration' = SUM(DR.Duration),
            'Contacts' = COUNT(CASE WHEN DI.Type > 1 THEN 1 ELSE NULL END),
            'Sales' = COUNT(CASE WHEN DI.Type = 3 THEN 1 ELSE NULL END)
            FROM [$db].[dbo].[DialingResults] DR
            CROSS APPLY (SELECT TOP 1 [Type]
                FROM  [$db].[dbo].[Dispos]
                WHERE Disposition = DR.CallStatus
                AND (GroupId = DR.GroupId OR IsSystem=1)
                AND (Campaign = DR.Campaign OR Campaign = '')
                ORDER BY [Description] Desc) DI
            WHERE DR.GroupId = :groupid$i
            AND DR.Rep != ''
            AND DR.CallType NOT IN (1,7,8,11)
            AND Duration > 0
            AND DR.CallStatus NOT IN (
                'CR_CEPT', 'CR_CNCT/CON_PAMD', 'CR_NOANS',
                'CR_NORB', 'CR_BUSY', 'CR_DROPPED', 'CR_FAXTONE',
                'CR_FAILED', 'CR_DISCONNECTED', 'CR_CNCT/CON_CAD',
                'CR_CNCT/CON_PVD', ' ', 'CR_HANGUP', 'Inbound')
            AND DR.Date >= :fromdate$i
            AND DR.Date < :todate$i";

            list($where, $extrabind) = $this->campaignClause('DR', $i, $campaign);
            $sql .= " $where";
            $bind = array_merge($bind, $extrabind);

            $sql .= "
                GROUP BY DR.Rep, DR.Campaign";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp
        GROUP BY Rep, Campaign;

        SELECT TOP 10
        Rep, Campaign, SUM(Contacts) as Contacts, SUM(Sales) as Sales, SUM(Duration) as [Talk Secs]
        FROM #temp
        GROUP BY Rep, Campaign
        ORDER BY SUM(Sales) DESC;

        SELECT Rep, SUM(Contacts) as Contacts, SUM(Sales) as Sales, SUM(Duration) as [Talk Secs]
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

        $result = $this->getAvgWaitTime();

        $summ = [];
        $reps = [];
        $avgs = [];
        $table = [];

        foreach ($result as $rec) {
            if (!isset($summ[$rec['Rep']])) {
                $summ[$rec['Rep']]['Rep'] = $rec['Rep'];
                $summ[$rec['Rep']]['Duration'] = 0;
                $summ[$rec['Rep']]['Cnt'] = 0;
                $summ[$rec['Rep']]['Avg'] = 0;
            }

            $summ[$rec['Rep']]['Duration'] += $rec['Duration'];
            $summ[$rec['Rep']]['Cnt'] += $rec['Cnt'];
            $summ[$rec['Rep']]['Avg'] = round($summ[$rec['Rep']]['Duration'] / $summ[$rec['Rep']]['Cnt']);

            $table[] = [
                'Rep' => $rec['Rep'],
                'Campaign' => $rec['Campaign'],
                'Avg' => round($rec['Duration'] / $rec['Cnt']),
            ];
        }

        // sort summ aray by avg desc
        usort($summ, function ($a, $b) {
            return $b['Avg'] <=> $a['Avg'];
        });

        // sort table by avg desc
        usort($table, function ($a, $b) {
            return $b['Avg'] <=> $a['Avg'];
        });

        // remove any with zero avgs
        foreach ($summ as $i => $rec) {
            if ($rec['Avg'] == 0) {
                unset($summ[$i]);
            }
        }
        foreach ($table as $i => $rec) {
            if ($rec['Avg'] == 0) {
                unset($table[$i]);
            }
        }

        // take top 10 from each
        $summ = array_slice($summ, 0, 10);
        $table = array_slice($table, 0, 10);

        foreach ($summ as $rec) {
            $reps[] = $rec['Rep'];
            $avgs[] = $rec['Avg'];
        }

        return [
            'Table' => $table,
            'Reps' => $reps,
            'Avgs' => $avgs,
        ];
    }

    private function getAvgWaitTime()
    {
        $campaign = $this->campaign;
        $dateFilter = $this->dateFilter;

        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $bind = [];

        $sql = 'SELECT Rep, Campaign, SUM(Duration) as Duration, SUM(Cnt) as Cnt FROM (';
        $union = '';
        foreach ($this->databases as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $startDate;
            $bind['todate' . $i] = $endDate;

            $sql .= " $union SELECT Rep, Campaign, SUM(Duration) as Duration, COUNT(AA.id) as Cnt
                FROM [$db].[dbo].[AgentActivity] AA
                WHERE [Action] = 'Waiting'
                AND AA.Duration > 0
                AND AA.Date >= :fromdate$i
                AND AA.Date < :todate$i
                AND AA.GroupId = :groupid$i";

            list($where, $extrabind) = $this->campaignClause('DR', $i, $campaign);
            $sql .= " $where";
            $bind = array_merge($bind, $extrabind);

            $sql .= "
                GROUP BY AA.Rep, AA.Campaign";

            $union = 'UNION ALL';
        }
        $sql .= ") tmp GROUP BY Rep, Campaign";

        return $this->runSql($sql, $bind);
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

        $total_calls = $total_calls[0];
        $prev_total_calls = $prev_total_calls[0];

        if ($prev_total_calls['Total'] == 0) {
            $pctdiff = null;
            $pctsign = null;
            $ntc = 1;  // nothing to compare
        } else {
            $pctdiff = ($total_calls['Total'] - $prev_total_calls['Total']) / $prev_total_calls['Total'] * 100;
            $pctsign = $pctdiff < 0 ? 0 : 1;
            $pctdiff = round(abs($pctdiff));
            $ntc = 0;
        }

        $total_dials = [
            'total' => $total_calls['Total'],
            'pct_change' => $pctdiff,
            'pct_sign' => $pctsign,
            'ntc' => $ntc,
        ];

        if ($prev_total_calls['Contacts'] == 0) {
            $pctdiff = null;
            $pctsign = null;
            $ntc = 1;  // nothing to compare
        } else {
            $pctdiff = ($total_calls['Contacts'] - $prev_total_calls['Contacts']) / $prev_total_calls['Contacts'] * 100;
            $pctsign = $pctdiff < 0 ? 0 : 1;
            $pctdiff = round(abs($pctdiff));
            $ntc = 0;
        }

        $total_contacts = [
            'total' => $total_calls['Contacts'],
            'pct_change' => $pctdiff,
            'pct_sign' => $pctsign,
            'ntc' => $ntc,
        ];

        $rate = $total_calls['Total'] > 0 ? round($total_calls['Contacts'] / $total_calls['Total'] * 100, 2) : 0;
        $prev_rate = $prev_total_calls['Total'] > 0 ? round($prev_total_calls['Contacts'] / $prev_total_calls['Total'] * 100, 2) : 0;

        if ($prev_rate == 0) {
            $pctdiff = null;
            $pctsign = null;
            $ntc = 1;  // nothing to compare
        } else {
            $pctdiff = ($rate - $prev_rate) / $prev_rate * 100;
            $pctsign = $pctdiff < 0 ? 0 : 1;
            $pctdiff = round(abs($pctdiff));
            $ntc = 0;
        }

        $contact_rate = [
            'rate' => $rate,
            'pct_change' => $pctdiff,
            'pct_sign' => $pctsign,
            'ntc' => $ntc,
        ];

        $details = $this->filterDetails($this->dateFilter, $this->campaign);

        return [
            'total_dials' => $total_dials,
            'total_contacts' => $total_contacts,
            'contact_rate' => $contact_rate,
            'details' => $details,
        ];
    }

    /**
     * query total calls
     *
     * @param boolean $prev
     * @return array
     */
    private function getTotalCalls($prev = false)
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

        $bind = [];

        $sql = 'SELECT COUNT(*) as [Total],
        ISNULL(SUM(CASE WHEN [Type] > 1 THEN 1 ELSE 0 END),0) as [Contacts]
        FROM (';

        $union = '';
        foreach ($this->databases as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $startDate;
            $bind['todate' . $i] = $endDate;

            $sql .= " $union SELECT
            IsNull((SELECT TOP 1 DI.[Type]
            FROM [$db].[dbo].[Dispos] DI
            WHERE Disposition=DR.CallStatus
            AND (GroupId=DR.GroupId OR IsSystem=1)
            AND (Campaign=DR.Campaign OR Campaign='')
            ORDER BY [Description] Desc), 0) as [Type]
            FROM [$db].[dbo].[DialingResults] DR
            WHERE DR.CallType NOT IN (1,7,8,11)
            AND DR.CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','TRANSFERRED','PARKED','Inbound')
            AND DR.Date >= :fromdate$i
            AND DR.Date < :todate$i
            AND DR.GroupId = :groupid$i";

            list($where, $extrabind) = $this->campaignClause('DR', $i, $campaign);
            $sql .= " $where";
            $bind = array_merge($bind, $extrabind);

            $union = 'UNION ALL';
        }
        $sql .= ") tmp";

        return $this->runSql($sql, $bind);
    }

    public function agentCallStatus(Request $request)
    {
        $this->getSession($request);

        $result = $this->getAgentCallStatus();

        $reps = array_column($result[0], 'Rep');
        $dispos = array_column($result[1], 'CallStatus');
        $stats = $result[2];

        // load up our disposition array with 0's for each rep
        $dispositions = [];
        foreach ($dispos as $dispo) {
            $dispositions[$dispo] = array_fill(0, count($reps), 0);
        }

        // loop thru reps and fill in dispositon counts
        foreach ($reps as $i => $rep) {
            foreach ($stats as $stat) {
                if ($stat['Rep'] == $rep) {
                    $dispositions[$stat['CallStatus']][$i] = (int) $stat['Count'];
                }
            }
        }

        if (isset($dispositions[''])) {
            $dispositions['[No Status]'] = $dispositions[''];
            unset($dispositions['']);
        }

        // Top 10 array for dispo charts
        $dispos = [];
        foreach ($dispositions as $disponame => $disporec) {
            $dispos[$disponame] = array_sum($disporec);
        }
        arsort($dispos, SORT_NUMERIC);

        $dispos = array_slice($dispos, 0, 10);

        return [
            'agent_call_status' => [
                'reps' => $reps,
                'dispositions' => $dispositions,
            ],
            'top10_dispos' => [
                'dispositions' => array_keys($dispos),
                'counts' => array_values($dispos),
            ],
        ];
    }

    private function getAgentCallStatus()
    {
        $campaign = $this->campaign;
        $dateFilter = $this->dateFilter;

        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $sql = "SET NOCOUNT ON;

        SELECT Rep, CallStatus, 'Count' = SUM([Count])
        INTO #temp
        FROM (";
        $union = '';
        foreach ($this->databases as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $startDate;
            $bind['todate' . $i] = $endDate;

            $sql .= " $union SELECT DR.Rep, DR.CallStatus, 'Count' = 1
            FROM [$db].[dbo].[DialingResults] DR
--            WITH (INDEX(IX_Billing))
            WHERE DR.CallType NOT IN (1,7,8,11)
            AND DR.CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound')
            AND DR.Duration > 0
            AND DR.Rep != ''
            AND DR.Date >= :fromdate$i
            AND DR.Date < :todate$i
            AND DR.GroupId = :groupid$i ";

            list($where, $extrabind) = $this->campaignClause('DR', $i, $campaign);
            $sql .= " $where";
            $bind = array_merge($bind, $extrabind);

            $union = 'UNION ALL';
        }

        $sql .= ") tmp GROUP BY Rep, CallStatus;

        SELECT TOP 10 Rep, 'Total' = SUM([Count])
        INTO #reps
        FROM #temp
        GROUP BY Rep ORDER BY [Total] DESC

        SELECT DISTINCT CallStatus
        INTO #stats
        FROM #temp
        WHERE Rep IN (SELECT Rep FROM #reps)

        SELECT Rep FROM #reps ORDER BY Rep

        SELECT CallStatus FROM #stats ORDER BY CallStatus

        SELECT Rep, CallStatus, [Count]
        FROM #temp
        WHERE Rep IN (SELECT Rep FROM #reps)";

        return $this->runMultiSql($sql, $bind);
    }
}
