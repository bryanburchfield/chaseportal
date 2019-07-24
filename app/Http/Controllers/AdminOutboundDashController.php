<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            'datefilter' => $this->dateFilter,
            'inorout' => $this->inorout,
            'campaign_list' => $campaigns,
            'curdash' => 'adminoutbounddash',
            'jsfile' => $jsfile,
            'cssfile' => $cssfile,
        ];
        return view('adminoutbounddash')->with($data);
    }

    public function callVolume(Request $request, $prev = false)
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
        $new_result['pct_change'] = $pctdiff;
        $new_result['pct_sign'] = $pctsign;
        $new_result['ntc'] = $ntc;

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
            'campaign' => $campaign,
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
        foreach (Auth::user()->getDatabaseArray() as $db) {
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
}
