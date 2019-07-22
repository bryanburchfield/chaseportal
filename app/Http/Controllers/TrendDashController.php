<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Campaign;
use App\DialingResult;
use App\AgentActivity;

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
        $new_result['outbound_duration'] = $outbound_duration;
        $new_result['total_inbound_duration'] = $total_inbound_duration;
        $new_result['total_outbound_duration'] = $total_outbound_duration;
        $new_result['duration_time'] = $duration_time;
        $new_result['total'] = $total;


        list($campaign, $details) = $this->filterDetails();

        $return['call_volume'] = $new_result;
        $return['call_volume']['campaign'] = $campaign;
        $return['call_volume']['details'] = $details;

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

    public function callDetails(Request $request)
    {
        $this->getSession($request);

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
        'Call Count' = SUM(CASE WHEN Action IN ('Call', 'ManualCall', 'InboundCall') THEN 1 ELSE 0 END),
        'Call Time' = CAST(SUM(CASE WHEN Action IN ('Call', 'ManualCall', 'InboundCall') THEN Duration ELSE 0 END) AS INTEGER),
        'Wrap Up Time' = CAST(SUM(CASE WHEN Action = 'Disposition' THEN Duration ELSE 0 END) AS INTEGER)";

        $query = AgentActivity::select(DB::raw($select));

        $query->where('Rep', '!=', '')
            ->where('Date', '>=', $startDate)
            ->where('Date', '<', $endDate)
            ->where('GroupId', Auth::user()->group_id);

        if (!empty($campaign) && $campaign != 'Total') {
            $query->where('Campaign', $campaign);
        }

        $query->groupBy(DB::raw($xAxis));

        $result1 = $query->get()->toArray();

        // We have to get HoldTime from another table, then merge it in.  sigh....

        $select = "$xAxis Time,
        'Hold Time' = SUM(CASE WHEN HoldTime <= 0 THEN 0 ELSE HoldTime END)";

        $query = DialingResult::select(DB::raw($select));

        $query->where('Rep', '!=', '')
            ->where('CallType', 1)
            ->whereNotNull('CallStatus')
            ->whereNotIn('CallStatus', ['CR_CNCT/CON_CAD', 'CR_CNCT/CON_PVD', 'Inbound', 'TRANSFERRED', 'PARKED'])
            ->where('Date', '>=', $startDate)
            ->where('Date', '<', $endDate)
            ->where('GroupId', Auth::user()->group_id);

        if (!empty($campaign) && $campaign != 'Total') {
            $query->where('Campaign', $campaign);
        }

        $query->groupBy(DB::raw($xAxis));

        $result2 = $query->get()->toArray();

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
            ],
        ];

        $result = $this->combineArrays($result1, $result2, $params['zeroRec']);

        $result = $this->formatVolume($result, $params);

        // now format the xAxis datetimes
        $details = array_map(array(&$this, $mapFunction), $result);

        // Now calculate avg handle time
        foreach ($details as &$rec) {
            $rec['Avg Handle Time'] = (empty($rec['Call Count'])) ? 0 : intval(($rec['Call Time'] + $rec['Wrap Up Time'] + $rec['Hold Time']) / $rec['Call Count']);
        }

        // now format the results
        $avg_handle_time = [];

        $time_labels = [];
        $calls = [];
        $num_calls = 0;
        $calltimes = 0;
        $avg_ht = 0;
        $wrapup = [];
        $holdtime = [];

        foreach ($details as $r) {
            array_push($time_labels, $r['Time']);
            array_push($calls, round($r['Call Time'] / 60));
            array_push($holdtime, round($r['Hold Time'] / 60));
            array_push($wrapup, round($r['Wrap Up Time'] / 60));
            $calltimes += round($r['Call Time'] / 60);
            $num_calls += $r['Call Count'];

            $avg = empty($r['Call Count']) ? 0 : round(($r['Call Time'] / 60 + $r['Hold Time'] / 60 + $r['Wrap Up Time'] / 60) / $r['Call Count']);
            array_push($avg_handle_time, $avg);

            $avg_ht += $avg;
        }

        $avg_ht = round($avg_ht / count($result[0]));
        $avg_call_time = !empty($num_calls) ? round($calltimes / $num_calls) : 0;

        $new_result['datetime'] = $time_labels;
        $new_result['calls'] = $calls;
        $new_result['hold_time'] = $holdtime;
        $new_result['wrapup_time'] = $wrapup;
        $new_result['avg_handle_time'] = $avg_handle_time;
        $new_result['avg_call_time'] = $avg_call_time;
        $new_result['avg_ht'] = $avg_ht;

        $return['call_details'] = $new_result;
        echo json_encode($return);
    }

    private function combineArrays($arr, $hold, $zeroRec)
    {
        // This is certainly not the most efficient way to do this....
        // first, go thru time recs and add hold time if we can find one
        foreach ($arr as &$rec) {
            $found = array_search($rec['Time'], array_column($hold, 'Time'));
            if ($found === false) {
                $rec['Hold Time'] = 0;
            } else {
                $rec['Hold Time'] = $hold[$found]['Hold Time'];
            }
        }

        // Now, go thru hold recs and add a blank time rec
        $newRecs = [];
        foreach ($hold as $holdRec) {
            $found = array_search($holdRec['Time'], array_column($arr, 'Time'));
            if ($found === false) {
                $zeroRec['Time'] = $holdRec['Time'];
                $zeroRec['Hold Time'] = $holdRec['Hold Time'];
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
        $timeZoneName = Auth::user()->tz;

        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $select = "Rep,
        'Total Calls' = COUNT(CallStatus),
        'Duration' = SUM(HandleTime)";

        $query = DialingResult::select(DB::raw($select));

        $query->whereNotIn('CallType', [7, 8])
            ->whereNotIn('CallStatus', ['CR_CNCT/CON_CAD', 'CR_CNCT/CON_PVD', 'Inbound', 'TRANSFERRED', 'PARKED'])
            ->where('HandleTime', '!=', 0)
            ->where('Duration', '!=', 0)
            ->where('Date', '>=', $startDate)
            ->where('Date', '<', $endDate)
            ->where('GroupId', Auth::user()->group_id);

        if (!empty($campaign) && $campaign != 'Total') {
            $query->where('Campaign', $campaign);
        }

        $query->groupBy('Rep');

        $result = $query->get()->toArray();

        // now process result
        $agent_labels = [];
        $total_calls = [];
        $call_duration = [];
        $cnt = 0;
        $avg_ct = 0;
        $avg_cc = 0;

        foreach ($result as $r) {
            if ($r['Rep'] != null) {
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

        $new_result['rep'] = $agent_labels;
        $new_result['duration'] = $call_duration;
        $new_result['total_calls'] = $total_calls;
        $new_result['avg_ct'] = $avg_ct;
        $new_result['avg_cc'] = $avg_cc;

        $return['agent_calltime'] = $new_result;
        echo json_encode($return);
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
            $xAxis = "CAST(CAST(CONVERT(datetimeoffset, Date) AT TIME ZONE '$timeZoneName' AS DATE) AS DATETIME)
            ";
        }

        $select = "$xAxis Time,
        'Handled Calls' = COUNT(CASE WHEN HoldTime < 20 AND CallStatus <> 'CR_HANGUP' THEN 1 ELSE NULL END),
        'Total Inbound Calls' = COUNT(CallStatus)";

        $query = DialingResult::select(DB::raw($select));

        $query->where('CallType', 1)
            ->whereNotIn('CallStatus', ['CR_CNCT/CON_CAD', 'CR_CNCT/CON_PVD', 'Inbound', 'TRANSFERRED', 'PARKED'])
            ->where('Date', '>=', $startDate)
            ->where('Date', '<', $endDate)
            ->where('GroupId', Auth::user()->group_id);

        if (!empty($campaign) && $campaign != 'Total') {
            $query->where('Campaign', $campaign);
        }

        $query->groupBy(DB::raw($xAxis));

        $result = $query->get()->toArray();

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

        // now process result
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

        $new_result['time'] = $time_labels;
        $new_result['total'] = $total_calls;
        $new_result['handled_calls'] = $handled_calls;
        $new_result['servicelevel'] = $servicelevel;
        $new_result['avg'] = $avg_sl;

        $return['service_level'] = $new_result;
        echo json_encode($return);
    }
}
