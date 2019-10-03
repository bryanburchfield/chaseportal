<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\DashTraits;

class AdminDashController extends Controller
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

        $jsfile[] = "admindash.js";
        $cssfile[] = "admindash.css";

        $data = [
            'isApi' => $this->isApi,
            'campaign' => $this->campaign,
            'dateFilter' => $this->dateFilter,
            'campaign_list' => $campaigns,
            'curdash' => 'admindash',
            'jsfile' => $jsfile,
            'cssfile' => $cssfile,
        ];
        return view('admindash')->with($data);
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
        $details = $this->filterDetails();

        // cards to be populated
        $calls_offered = [
            'count' => 0,
            'pct_change' => 0,
            'pct_sign' => 0,
            'ntc' => 0,
        ];
        $calls_answered = [
            'duration' => 0,
            'count' => 0,
            'average' => 0,
            'min' => null,
            'max' => null,
            'pct_change' => 0,
            'pct_sign' => 0,
            'ntc' => 0,
        ];
        $calls_missed = [
            'count' => 0,
            'abandoned' => 0,
            'voicemail' => 0,
            'pct_change' => 0,
            'pct_sign' => 0,
            'ntc' => 0,
        ];
        $talk_time = [
            'duration' => 0,
            'count' => 0,
            'average' => 0,
            'min' => null,
            'max' => null,
            'pct_change' => 0,
            'pct_sign' => 0,
            'ntc' => 0,
        ];
        $call_volume = [
            'time_labels' => [],
            'total_calls' => [],
            'voicemails' => [],
            'abandoned' => [],
            'answered' => [],
        ];
        $call_duration = [
            'time_labels' => [],
            'duration' => [],
        ];

        // Prev tots for rate change calcs
        $prev_calls_offered = 0;
        $prev_answer_count = 0;
        $prev_calls_missed = 0;
        $prev_answer_count = 0;
        $prev_answer_duration = 0;
        $prev_talk_count = 0;
        $prev_talk_duration = 0;

        foreach ($result[0] as $r) {
            if ($this->byHour($this->dateFilter)) {
                $datetime = date("g:i", strtotime($r['Time']));
            } else {
                $datetime = date("D n/j/y", strtotime($r['Time']));
            }

            array_push($call_volume['time_labels'], $datetime);
            array_push($call_volume['total_calls'], $r['Count']);
            array_push($call_volume['voicemails'], $r['Voicemails']);
            array_push($call_volume['abandoned'], $r['Abandoned Calls']);
            array_push($call_volume['answered'], $r['Answered Calls']);

            $calls_offered['count'] += $r['Count'];

            $calls_missed['count'] += $r['Voicemails'] + $r['Abandoned Calls'];
            $calls_missed['abandoned'] += $r['Abandoned Calls'];
            $calls_missed['voicemail'] += $r['Voicemails'];

            $calls_answered['count'] += $r['Answered Calls'];
            $calls_answered['duration'] += $r['Answered Duration'];

            if ($calls_answered['min'] === null && (int) $r['Answered Duration Min'] > 0) {
                $calls_answered['min'] = $r['Answered Duration Min'];
            } else {
                if ($calls_answered['min'] > $r['Answered Duration Min'] && (int) $r['Answered Duration Min'] > 0) {
                    $calls_answered['min'] = $r['Answered Duration Min'];
                }
            }

            if ($calls_answered['max'] === null && (int) $r['Answered Duration Max'] > 0) {
                $calls_answered['max'] = $r['Answered Duration Max'];
            } else {
                if ($calls_answered['max'] < $r['Answered Duration Max'] && (int) $r['Answered Duration Max'] > 0) {
                    $calls_answered['max'] = $r['Answered Duration Max'];
                }
            }

            // only count talk times > 0
            if ($r['Duration'] > 0) {
                $talk_time['count'] += $r['Count'];
                $talk_time['duration'] += $r['Duration'];

                if ($talk_time['min'] === null && (int) $r['Duration Min'] > 0) {
                    $talk_time['min'] = $r['Duration Min'];
                } else {
                    if ($talk_time['min'] > $r['Duration Min'] && (int) $r['Duration Min'] > 0) {
                        $talk_time['min'] = $r['Duration Min'];
                    }
                }

                if ($talk_time['max'] === null && (int) $r['Duration Max'] > 0) {
                    $talk_time['max'] = $r['Duration Max'];
                } else {
                    if ($talk_time['max'] < $r['Duration Max'] && (int) $r['Duration Max'] > 0) {
                        $talk_time['max'] = $r['Duration Max'];
                    }
                }
            }
        }

        foreach ($result[1] as $r) {
            if ($this->byHour($this->dateFilter)) {
                $datetime = date("g:i", strtotime($r['Time']));
            } else {
                $datetime = date("D n/j/y", strtotime($r['Time']));
            }

            array_push($call_duration['time_labels'], $datetime);
            array_push($call_duration['duration'], $r['Duration']);
        }

        $calls_answered['average'] = ($calls_answered['count'] > 0) ? round($calls_answered['duration'] / $calls_answered['count']) : 0;
        $talk_time['average'] = ($talk_time['count'] > 0) ? round($talk_time['duration'] / $talk_time['count']) : 0;

        // Previous stats
        foreach ($prev_result[0] as $r) {
            $prev_calls_offered += $r['Count'];
            $prev_calls_missed += $r['Voicemails'] + $r['Abandoned Calls'];

            $prev_answer_count += $r['Answered Calls'];
            $prev_answer_duration += $r['Answered Duration'];

            $prev_talk_count += $r['Count'];
            $prev_talk_duration += $r['Duration'];
        }

        $prev_answer_average = ($prev_answer_count > 0) ? round($prev_answer_duration / $prev_answer_count) : 0;
        $prev_talk_average = ($prev_talk_count > 0) ? round($prev_talk_duration / $prev_talk_count) : 0;

        if ($prev_calls_offered == 0) {
            $calls_offered['pct_change'] = null;
            $calls_offered['pct_sign'] = null;
            $calls_offered['ntc'] = 1;  // nothing to compare
        } else {
            $calls_offered['pct_change'] = ($calls_offered['count'] - $prev_calls_offered) / $prev_calls_offered * 100;
            $calls_offered['pct_sign'] = $calls_offered['pct_change'] < 0 ? 0 : 1;
            $calls_offered['pct_change'] = round(abs($calls_offered['pct_change']));
            $calls_offered['ntc'] = 0;
        }

        if ($prev_calls_missed == 0) {
            $calls_missed['pct_change'] = null;
            $calls_missed['pct_sign'] = null;
            $calls_missed['ntc'] = 1;  // nothing to compare
        } else {
            $calls_missed['pct_change'] = ($calls_missed['count'] - $prev_calls_missed) / $prev_calls_missed * 100;
            $calls_missed['pct_sign'] = $calls_missed['pct_change'] < 0 ? 0 : 1;
            $calls_missed['pct_change'] = round(abs($calls_missed['pct_change']));
            $calls_missed['ntc'] = 0;
        }

        if ($prev_answer_average == 0) {
            $calls_answered['pct_change'] = null;
            $calls_answered['pct_sign'] = null;
            $calls_answered['ntc'] = 1;  // nothing to compare
        } else {
            $calls_answered['pct_change'] = ($calls_answered['average'] - $prev_answer_average) / $prev_answer_average * 100;
            $calls_answered['pct_sign'] = $calls_answered['pct_change'] < 0 ? 0 : 1;
            $calls_answered['pct_change'] = round(abs($calls_answered['pct_change']));
            $calls_answered['ntc'] = 0;
        }

        if ($prev_talk_average == 0) {
            $talk_time['pct_change'] = null;
            $talk_time['pct_sign'] = null;
            $talk_time['ntc'] = 1;  // nothing to compare
        } else {
            $talk_time['pct_change'] = ($talk_time['average'] - $prev_talk_average) / $prev_talk_average * 100;
            $talk_time['pct_sign'] = $talk_time['pct_change'] < 0 ? 0 : 1;
            $talk_time['pct_change'] = round(abs($talk_time['pct_change']));
            $talk_time['ntc'] = 0;
        }

        return ['call_volume' => [
            'calls_offered' => $calls_offered,
            'calls_answered' => $calls_answered,
            'calls_missed' => $calls_missed,
            'talk_time' => $talk_time,
            'call_volume' => $call_volume,
            'call_duration' => $call_duration,
            'details' => $details,
            'missed_call_details' => [],
        ]];
    }

    /**
     * Query call volume
     *
     * @param boolean $prev
     * @return void
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
        SUM([Count]) AS 'Count',
        SUM([Completed Calls]) AS 'Completed',
        SUM([Answered Calls]) AS 'Answered Calls',
        SUM([Answered Duration]) AS 'Answered Duration',
        MIN([Answered Duration Min]) AS 'Answered Duration Min',
        MAX([Answered Duration Max]) AS 'Answered Duration Max',
        SUM([Voicemails]) AS 'Voicemails',
        SUM([Abandoned Calls]) AS 'Abandoned Calls',
        SUM([Dropped Calls]) AS 'Dropped Calls',
        SUM([Duration]) AS 'Duration',
        MIN([Duration Min]) AS 'Duration Min',
        MAX([Duration Max]) AS 'Duration Max'
        FROM (";

        $union = '';
        foreach ($this->databases as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $startDate;
            $bind['todate' . $i] = $endDate;

            $sql .= " $union SELECT $xAxis as 'Time',
    'Count' = SUM(1),
    'Completed Calls' = SUM(CASE WHEN DR.CallStatus NOT IN ('','CR_BUSY','CR_CEPT','CR_CNCT/CON_CAD','CR_CNCT/CON_PAMD',
    'CR_CNCT/CON_PVD','CR_DISCONNECTED','CR_DROPPED','CR_FAILED','CR_FAXTONE','CR_HANGUP',
    'CR_NOANS','CR_NORB','Inbound','Inbound Voicemail') THEN 1 ELSE 0 END),
    'Answered Calls' = SUM(CASE WHEN DR.CallStatus NOT IN ( 'CR_CEPT', 'CR_CNCT/CON_PAMD', 'CR_NOANS', 'CR_NORB', 'CR_BUSY',
    'CR_DROPPED', 'CR_FAXTONE', 'CR_FAILED', 'CR_DISCONNECTED',
    'CR_HANGUP', 'Inbound Voicemail') THEN 1 ELSE 0 END),
    'Answered Duration' = SUM(CASE WHEN DR.CallStatus NOT IN ( 'CR_CEPT', 'CR_CNCT/CON_PAMD', 'CR_NOANS', 'CR_NORB', 'CR_BUSY',
    'CR_DROPPED', 'CR_FAXTONE', 'CR_FAILED', 'CR_DISCONNECTED',
    'CR_HANGUP', 'Inbound Voicemail') THEN DR.Duration ELSE 0 END),
    'Answered Duration Min' = MIN(CASE WHEN DR.CallStatus NOT IN ( 'CR_CEPT', 'CR_CNCT/CON_PAMD', 'CR_NOANS', 'CR_NORB', 'CR_BUSY',
    'CR_DROPPED', 'CR_FAXTONE', 'CR_FAILED', 'CR_DISCONNECTED',
    'CR_HANGUP', 'Inbound Voicemail') THEN DR.Duration ELSE NULL END),
    'Answered Duration Max' = MAX(CASE WHEN DR.CallStatus NOT IN ( 'CR_CEPT', 'CR_CNCT/CON_PAMD', 'CR_NOANS', 'CR_NORB', 'CR_BUSY',
    'CR_DROPPED', 'CR_FAXTONE', 'CR_FAILED', 'CR_DISCONNECTED',
    'CR_HANGUP', 'Inbound Voicemail') THEN DR.Duration ELSE NULL END),
    'Voicemails' = SUM(CASE WHEN DR.CallStatus='Inbound Voicemail' THEN 1 ELSE 0 END),
    'Abandoned Calls' = SUM(CASE WHEN DR.CallStatus='CR_HANGUP' THEN 1 ELSE 0 END),
    'Dropped Calls' = SUM(CASE WHEN DR.CallStatus='CR_DROPPED' THEN 1 ELSE 0 END),
    'Duration' = SUM(DR.Duration),
    'Duration Min' = MIN(DR.Duration),
    'Duration Max' = MAX(DR.Duration)
            FROM [$db].[dbo].[DialingResults] DR
            WHERE DR.CallType IN (1,11)
            AND DR.CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound')
            AND Duration > 0
            AND DR.Date >= :fromdate$i
            AND DR.Date < :todate$i
            AND DR.GroupId = :groupid$i ";

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

        $inResult = $this->inboundVolume($result, $params);
        $durResult = $this->callDuration($result, $params);

        return [
            $inResult,
            $durResult,
        ];
    }

    /**
     * parse inbound stats
     *
     * @param array $result
     * @param array $params
     * @return void
     */
    private function inboundVolume($result, $params)
    {
        // define recs with no data to compare against or insert if we need to fill in gaps
        $zeroRec = [
            'Time' => '',
            'Count' => 0,
            'Completed Calls' => 0,
            'Answered Calls' => 0,
            'Answered Duration' => 0,
            'Answered Duration Min' => 0,
            'Answered Duration Max' => 0,
            'Voicemails' => 0,
            'Abandoned Calls' => 0,
            'Dropped Calls' => 0,
            'Duration' => 0,
            'Duration Min' => 0,
            'Duration Max' => 0,
        ];

        return ($this->zeroRecs($result, $zeroRec, $params));
    }

    /**
     * return call duration
     *
     * @param array $result
     * @param array $params
     * @return void
     */
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
            'Duration' => 0,
        ];

        return ($this->zeroRecs($duration, $zeroRec, $params));
    }

    /**
     * return average hold time
     *
     * @param Request $request
     * @return void
     */
    public function avgHoldTime(Request $request)
    {
        $this->getSession($request);

        $average_hold_time = $this->getAvgHoldTime();
        $prev_average_hold_time = $this->getAvgHoldTime(true);

        if (empty($average_hold_time['Total Calls'])) {
            $average_hold_time['Total Calls'] = 0;
            $average_hold_time['Hold Secs'] = 0;
            $average_hold_time['MinHold'] = 0;
            $average_hold_time['MaxHold'] = 0;
        }

        if (empty($prev_average_hold_time['Total Calls'])) {
            $prev_average_hold_time['Total Calls'] = 0;
            $prev_average_hold_time['Hold Secs'] = 0;
            $prev_average_hold_time['MinHold'] = 0;
            $prev_average_hold_time['MaxHold'] = 0;
        }

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

        return ['average_hold_time' => [
            'min_hold_time' => $average_hold_time['MinHold'],
            'max_hold_time' => $average_hold_time['MaxHold'],
            'avg_hold_time' => $avg_hold_time,
            'total_hold_time' => $total_hold_time,
            'pct_change' => $pctdiff,
            'pct_sign' => $pctsign,
            'ntc' => $ntc,
        ]];
    }

    /**
     * query average hold time
     *
     * @param boolean $prev
     * @return array
     */
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
        SUM(HoldTime) as 'Hold Secs',
        MIN(MinHold) as 'MinHold',
        MAX(MaxHold) as 'MaxHold'
        FROM (";
        $union = '';
        foreach ($this->databases as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $startDate;
            $bind['todate' . $i] = $endDate;

            $sql .= " $union SELECT
                'Cnt' = COUNT(CallStatus),
                'HoldTime' = SUM(HoldTime),
                'MinHold' = MIN(HoldTime),
                'MaxHold' = MAX(HoldTime)
                FROM [$db].[dbo].[DialingResults] DR
                WHERE CallType = 1
                AND CallStatus NOT IN('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound','TRANSFERRED','PARKED')
                AND HoldTime >= 0
                AND DR.Date >= :fromdate$i
                AND DR.Date < :todate$i
                AND DR.GroupId = :groupid$i";

            list($where, $extrabind) = $this->campaignClause('DR', $i, $campaign);
            $sql .= " $where";
            $bind = array_merge($bind, $extrabind);

            $union = 'UNION ALL';
        }
        $sql .= ") tmp";

        $result = $this->runSql($sql, $bind);

        return $result[0];
    }

    /**
     * return abandon rate
     *
     * @param Request $request
     * @return void
     */
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

        return [
            'abandon_rate' => [
                'abandon_calls' => $abandon_rate['Abandoned'],
                'abandon_rate' => $abandon_pct,
                'pct_change' => $pctdiff,
                'pct_sign' => $pctsign,
                'ntc' => $ntc,
            ],
        ];
    }

    /**
     * query abandon rate
     *
     * @param boolean $prev
     * @return array
     */
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
        foreach ($this->databases as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $startDate;
            $bind['todate' . $i] = $endDate;

            $sql .= " $union SELECT
            'Calls' = COUNT(CallStatus),
            'Abandoned' = SUM(CASE WHEN CallStatus='CR_HANGUP' THEN 1 ELSE 0 END)
            FROM [$db].[dbo].[DialingResults] DR
            WHERE CallType IN (1,11)
            AND CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound')
            AND DR.Duration > 0
            AND DR.Date >= :fromdate$i
            AND DR.Date < :todate$i
            AND DR.GroupId = :groupid$i";

            list($where, $extrabind) = $this->campaignClause('DR', $i, $campaign);
            $sql .= " $where";
            $bind = array_merge($bind, $extrabind);

            $sql .= "
            GROUP BY Campaign";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp";

        $result = $this->runSql($sql, $bind);

        return $result[0];
    }

    /**
     * return total sales
     *
     * @param Request $request
     * @return void
     */
    public function totalSales(Request $request)
    {
        $this->getSession($request);

        $result = $this->getTotalSales();
        $prev_result = $this->getTotalSales(true);

        $total_sales = $result['Sales'];
        $prev_total_sales = $prev_result['Sales'];

        if ($prev_total_sales == 0) {
            $pct_change = null;
            $pct_sign = null;
            $ntc = 1;  // nothing to compare
        } else {
            $pct_change = ($total_sales - $prev_total_sales) / $prev_total_sales * 100;
            $pct_sign = $pct_change < 0 ? 0 : 1;
            $pct_change = round(abs($pct_change), 0);
            $ntc = 0;
        }

        return ['total_sales' => [
            'sales' => $total_sales,
            'pct_change' => $pct_change,
            'pct_sign' => $pct_sign,
            'ntc' => $ntc,
        ]];
    }

    /**
     * query total sales
     *
     * @param boolean $prev
     * @return array
     */
    public function getTotalSales($prev = false)
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

        $sql = "SELECT 'Sales' = SUM(Sales)
        FROM (";
        $union = '';
        foreach ($this->databases as $i => $db) {

            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $startDate;
            $bind['todate' . $i] = $endDate;

            $sql .= " $union SELECT
            'Sales' = COUNT(CASE WHEN DI.Type = '3' THEN 1 ELSE NULL END)
            FROM [$db].[dbo].[DialingResults] DR
            CROSS APPLY (SELECT TOP 1 [Type]
                FROM  [$db].[dbo].[Dispos]
                WHERE Disposition = DR.CallStatus
                AND (GroupId = DR.GroupId OR IsSystem=1)
                AND (Campaign = DR.Campaign OR Campaign = '')
                ORDER BY [Description] Desc) DI
            WHERE DR.GroupId = :groupid$i
            AND DR.CallType IN (1,11)
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

            $union = 'UNION ALL';
        }

        $sql .= ") tmp";

        $result = $this->runSql($sql, $bind);

        return $result[0];
    }

    /**
     * return agent call count
     *
     * @param Request $request
     * @return void
     */
    public function agentCallCount(Request $request)
    {
        $this->getSession($request);

        list($bycamp, $byrep) = $this->getAgentCallCount();

        $call_count_table = deleteColumn($bycamp, 'Duration');
        $call_time_table = deleteColumn($bycamp, 'Count');

        // sort arrays
        usort($call_count_table, function ($a, $b) {
            return $b['Count'] <=> $a['Count'];
        });
        usort($call_time_table, function ($a, $b) {
            return $b['Duration'] <=> $a['Duration'];
        });

        // take top 10
        $call_count_table = array_slice($call_count_table, 0, 10);
        $call_time_table = array_slice($call_time_table, 0, 10);

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
        $call_time_reps = array_column(array_slice($byrep, 0, 10), 'Rep');
        $call_time_secs = array_column(array_slice($byrep, 0, 10), 'Duration');

        $call_time_hms = [];
        foreach ($call_time_secs as $d) {
            $call_time_hms[] = secondsToHms($d);
        }

        return [
            'call_count_table' => $call_count_table,
            'call_count_reps' => $call_count_reps,
            'call_count_counts' => $call_count_counts,
            'call_time_table' => $call_time_table,
            'call_time_reps' => $call_time_reps,
            'call_time_secs' => $call_time_secs,
            'call_time_hms' => $call_time_hms,
        ];
    }

    public function getAgentCallCount()
    {
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
        foreach ($this->databases as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $startDate;
            $bind['todate' . $i] = $endDate;

            $sql .= " $union SELECT DR.Rep, DR.Campaign,
            'Count' = COUNT(DR.CallStatus),
            'Duration' = SUM(DR.Duration)
            FROM [$db].[dbo].[DialingResults] DR
            WITH (INDEX(IX_Billing))
            WHERE DR.CallType IN (1,11)
            AND DR.CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound')
            AND Duration > 0
            AND DR.Date >= :fromdate$i
            AND DR.Date < :todate$i
            AND DR.GroupId = :groupid$i ";

            list($where, $extrabind) = $this->campaignClause('DR', $i, $campaign);
            $sql .= " $where";
            $bind = array_merge($bind, $extrabind);

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

        return $this->runMultiSql($sql, $bind);
    }

    /**
     * return service level
     *
     * @param Request $request
     * @return void
     */
    public function serviceLevel(Request $request)
    {
        $this->getSession($request);

        $result = $this->getServiceLevel($request);

        if (empty($result['Handled'])) {
            $result['Handled'] = 0;
        }
        if (empty($result['Count'])) {
            $result['Count'] = 0;
        }

        $handled = $result['Handled'];
        $count = $result['Count'];

        if (!$count) {
            $svc_level = 100;
        } else {
            $svc_level = $handled / $count * 100;
        }

        $rem = 100 - $svc_level;
        $svc_level = round($svc_level);

        return ['service_level' => [
            'service_level' => $svc_level,
            'remainder' => $rem
        ]];
    }

    public function getServiceLevel(Request $request)
    {
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
        foreach ($this->databases as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $startDate;
            $bind['todate' . $i] = $endDate;
            $bind['answersecs' . $i] = $answerSecs;

            $sql .= " $union SELECT 'Handled' = COUNT(CASE WHEN HoldTime < :answersecs$i AND CallStatus <> 'CR_HANGUP' AND CallStatus <> 'Inbound Voicemail' THEN 1 ELSE NULL END),
            'Count' = COUNT(CallStatus)
            FROM [$db].[dbo].[DialingResults] DR
            WHERE CallType = 1
            AND CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound','TRANSFERRED','PARKED')
            AND DR.Date >= :fromdate$i
            AND DR.Date < :todate$i
            AND DR.GroupId = :groupid$i";

            list($where, $extrabind) = $this->campaignClause('DR', $i, $campaign);
            $sql .= " $where";
            $bind = array_merge($bind, $extrabind);

            $union = 'UNION ALL';
        }
        $sql .= ") tmp";

        $result = $this->runSql($sql, $bind);

        return $result[0];
    }

    /**
     * return rep avg handle time
     *
     * @param Request $request
     * @return void
     */
    public function repAvgHandleTime(Request $request)
    {
        $this->getSession($request);

        list($bycamp, $byrep) = $this->getRepAvgHandleTime();

        $reps = [];
        $handletime = [];
        $handletimesecs = [];

        foreach ($byrep as $rec) {
            $reps[] = $rec['Rep'];
            $handletimesecs[] = $rec['AverageHandleTime'];
            $handletime[] = secondsToHms($rec['AverageHandleTime']);
        }

        $max_handle_time = count($handletimesecs) ? max($handletimesecs) : 0;

        if (count($handletimesecs)) {
            $handletimesecs = array_filter($handletimesecs);
            $total_avg_handle_time = round(array_sum($handletimesecs) / count($handletimesecs));
        } else {
            $total_avg_handle_time = 0;
        }

        $total_avg_handle_time = $max_handle_time != 0 ? round($total_avg_handle_time / $max_handle_time * 100) : 0;

        $remainder = 100 - $total_avg_handle_time;

        return [
            'reps' => $reps,
            'avg_handletime' => $handletime,
            'avg_handletimesecs' => $handletimesecs,
            'table' => $bycamp,
            'max_handle_time' => $max_handle_time,
            'total_avg_handle_time' => $total_avg_handle_time,
            'remainder' => $remainder
        ];
    }

    private function getRepAvgHandleTime()
    {
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
        foreach ($this->databases as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $startDate;
            $bind['todate' . $i] = $endDate;

            $sql .= " $union SELECT Rep, Campaign,
            Duration, CallStatus
            FROM [$db].[dbo].[DialingResults] DR
            WITH (INDEX(IX_Billing))
            WHERE CallType IN (1,11)
            AND CallStatus NOT IN('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound','TRANSFERRED','PARKED')
            AND HoldTime >= 0
            AND Duration > 0
            AND DR.Date >= :fromdate$i
            AND DR.Date < :todate$i
            AND DR.GroupId = :groupid$i ";

            list($where, $extrabind) = $this->campaignClause('DR', $i, $campaign);
            $sql .= " $where";
            $bind = array_merge($bind, $extrabind);

            $union = 'UNION ALL';
        }

        $sql .= ") tmp
        GROUP BY Rep, Campaign;

        SELECT TOP 10 Rep, Campaign,
        'AverageHandleTime' = [Duration]/[Count]
        FROM #temp
        ORDER BY 'AverageHandleTime' DESC;

        SELECT TOP 10 Rep,
        'AverageHandleTime' = SUM([Duration])/SUM([Count])
        FROM #temp
        GROUP BY Rep
        ORDER BY 'AverageHandleTime' DESC";

        return $this->runMultiSql($sql, $bind);
    }
}
