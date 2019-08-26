<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Campaign;
use \App\Traits\DashTraits;

class AgentDashController extends Controller
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

        $groupId = Auth::user()->group_id;
        $campaigns = Campaign::where('GroupId', $groupId)->where('IsActive', 1)->pluck('CampaignName')->toArray();
        natcasesort($campaigns);
        array_unshift($campaigns, 'Total');

        $jsfile[] = "agentdash.js";
        $cssfile[] = "agentdash.css";

        $data = [
            'isApi' => $this->isApi,
            'userId' => Session::get('userId'),
            'campaign' => $this->campaign,
            'datefilter' => $this->dateFilter,
            'inorout' => $this->inorout,
            'campaign_list' => $campaigns,
            'curdash' => 'agentdash',
            'jsfile' => $jsfile,
            'cssfile' => $cssfile,
        ];
        return view('agentdash')->with($data);
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

        return $result;

        $time_labels = [];
        $outbound = [];
        $inbound = [];
        $manual = [];
        $new_result = [];

        $tot_outbound = 0;
        $tot_inbound = 0;
        $tot_manual = 0;
        $tot_total = 0;
        $duration = 0;

        // Prev tots for rate change calcs
        $prev_calls_offered = 0;
        $prev_answer_count = 0;
        $prev_calls_missed = 0;
        $prev_answer_count = 0;
        $prev_answer_duration = 0;
        $prev_talk_count = 0;
        $prev_talk_duration = 0;


        foreach ($result as $r) {
            if ($this->byHour($this->dateFilter)) {
                $datetime = date("g:i", strtotime($r['Time']));
            } else {
                $datetime = date("n/j/y", strtotime($r['Time']));
            }

            $tot_outbound += $r['Outbound'];
            $tot_manual += $r['Manual'];
            $tot_inbound += $r['Inbound'];
            $duration += $r['Duration'];

            array_push($time_labels, $datetime);
            array_push($inbound, $r['Inbound']);
            array_push($outbound, $r['Outbound']);
            array_push($manual, $r['Manual']);
        }


        $tot_total = $tot_inbound + $tot_outbound + $tot_manual;

        if ($tot_total) {
            $avg_handle_time = Helpers::secondsToHms($duration / $tot_total);
        } else {
            $avg_handle_time = '00:00:00';
        }
        $duration = date('H:i:s', $duration);

        $new_result['time'] = $time_labels;
        $new_result['outbound'] = $outbound;
        $new_result['inbound'] = $inbound;
        $new_result['manual'] = $manual;
        $new_result['tot_outbound'] = $tot_outbound;
        $new_result['tot_inbound'] = $tot_inbound;
        $new_result['tot_manual'] = $tot_manual;
        $new_result['tot_total'] = $tot_total;
        $new_result['avg_handle_time'] = $avg_handle_time;
        $new_result['details'] = $details;

        return ['call_volume' => $new_result];
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
        SUM([Outbound]) AS 'Outbound',
        SUM([Inbound]) AS 'Inbound',
        SUM([Manual]) AS 'Manual',
        SUM([Duration]) AS 'Duration'
        FROM (";

        $union = '';
        foreach (Auth::user()->getDatabaseArray() as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['rep' . $i] = $this->rep;
            $bind['fromdate' . $i] = $startDate;
            $bind['todate' . $i] = $endDate;

            $sql .= " $union SELECT $xAxis as 'Time',
	        'Outbound' = SUM(CASE WHEN DR.CallType IN ('0','4','5','6','10','12','14','15') THEN 1 ELSE 0 END),
			'Inbound' = SUM(CASE WHEN DR.CallType IN ('1','11') THEN 1 ELSE 0 END),
			'Manual' = SUM(CASE WHEN DR.CallType IN ('2') THEN 1 ELSE 0 END),
            'Duration' = SUM(DR.Duration)
            FROM [$db].[dbo].[DialingResults] DR
            WHERE DR.Duration <> 0
            AND DR.CallType NOT IN ('7','8')
            AND DR.CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound','TRANSFERRED','PARKED','SMS Received','SMS Delivered')
            AND DR.GroupId = :groupid
            AND DR.Rep = :rep
            AND DR.Date >= :fromdate
			AND DR.Date < :todate
            GROUP BY $xAxis";

            $union = 'UNION ALL';
        }
        $sql .= ") tmp
        GROUP BY [Time]
        ORDER BY [Time]";


        // split the results into three arrays
        $params = [
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'modifier' => $modifier,
            'byHour' => $byHour,
            'format' => $format,
        ];

        $result = $this->runSql($sql, $bind);


        return $result;
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

    public function callStatusCount(Request $request)
    { }

    public function repPerformance(Request $request)
    { }
}
