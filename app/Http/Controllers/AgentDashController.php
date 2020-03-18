<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $campaigns = $this->campaignGroups();

        $jsfile[] = "agentdash.js";
        $cssfile[] = "agentdash.css";

        $data = [
            'isApi' => $this->isApi,
            'campaign' => $this->campaign,
            'dateFilter' => $this->dateFilter,
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
        $details = $this->filterDetails($this->dateFilter);

        $time_labels = [];
        $outbound = [];
        $inbound = [];
        $manual = [];

        $tot_outbound = 0;
        $tot_inbound = 0;
        $tot_manual = 0;
        $tot_total = 0;
        $duration = 0;

        foreach ($result as $r) {
            if ($this->byHour($this->dateFilter)) {
                $datetime = date("g:i", strtotime($r['Time']));
            } else {
                $datetime = date("D n/j/y", strtotime($r['Time']));
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
        $avg_handle_time = $this->secondsToHms($tot_total != 0 ? round($duration / $tot_total) : 0);

        return ['call_volume' => [
            'time' => $time_labels,
            'outbound' => $outbound,
            'inbound' => $inbound,
            'manual' => $manual,
            'tot_outbound' => $tot_outbound,
            'tot_inbound' => $tot_inbound,
            'tot_manual' => $tot_manual,
            'tot_total' => $tot_total,
            'avg_handle_time' => $avg_handle_time,
            'details' => $details,
        ]];
    }

    /**
     * Query call volume
     *
     * @param boolean $prev
     * @return void
     */
    private function getCallVolume()
    {
        $tz = Auth::user()->tz;

        $dateFilter = $this->dateFilter;
        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $fromDate = $fromDate->format('Y-m-d H:i:s');
        $toDate = $toDate->format('Y-m-d H:i:s');

        $byHour = $this->byHour($dateFilter);

        // group by date/hour or just date
        if ($byHour) {
            $mapFunction = 'dateTimeToHour';
            $format = 'Y-m-d H:i:s.000';
            $modifier = "+1 hour";
            $xAxis = "DATEADD(HOUR, DATEPART(HOUR, CONVERT(datetimeoffset, DR.Date) AT TIME ZONE '$tz'),
            CAST(CAST(CONVERT(datetimeoffset, DR.Date) AT TIME ZONE '$tz' AS DATE) AS DATETIME))";
        } else {
            $mapFunction = 'dateTimeToDay';
            $format = 'Y-m-d 00:00:00.000';
            $modifier = "+1 day";
            $xAxis = "CAST(CAST(CONVERT(datetimeoffset, DR.Date) AT TIME ZONE '$tz' AS DATE) AS DATETIME)
            ";
        }

        $bind = [];

        $sql = "SELECT Time,
        'Outbound' = SUM([Outbound]),
        'Inbound' = SUM([Inbound]),
        'Manual' = SUM([Manual]),
        'Duration' = SUM([Duration])
        FROM (";

        $union = '';
        foreach ($this->databases as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $fromDate;
            $bind['todate' . $i] = $toDate;
            $bind['rep' . $i] = $this->rep;

            $sql .= " $union SELECT $xAxis as 'Time',
	        'Outbound' = SUM(CASE WHEN DR.CallType NOT IN (1,2,11) THEN 1 ELSE 0 END),
			'Inbound' = SUM(CASE WHEN DR.CallType IN (1,11) THEN 1 ELSE 0 END),
			'Manual' = SUM(CASE WHEN DR.CallType IN (2) THEN 1 ELSE 0 END),
            'Duration' = SUM(DR.Duration)
            FROM [$db].[dbo].[DialingResults] DR
            WHERE DR.Duration <> 0
            AND DR.CallType NOT IN (7,8)
            AND DR.CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound','Inbound Voicemail','TRANSFERRED','PARKED','SMS Received','SMS Delivered')
            AND DR.GroupId = :groupid$i
            AND DR.Rep = :rep$i
            AND DR.Date >= :fromdate$i
			AND DR.Date < :todate$i
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
                'Outbound' => 0,
                'Inbound' => 0,
                'Manual' => 0,
                'Duration' => 0,
            ],
        ];

        $result = $this->formatVolume($result, $params);

        // now format the xAxis datetimes and return the results
        return array_map(array(&$this, $mapFunction), $result);
    }

    public function campaignStats(Request $request)
    {
        $this->getSession($request);

        $result = $this->getCampaignStats();

        // sort be campaign
        ksort($result, SORT_NATURAL | SORT_FLAG_CASE);

        $total_talk_time = 0;
        $top_ten = [];

        // Compute averages
        foreach ($result as $campaign => &$rec) {
            // Delete any with no calls
            if ($rec['Calls'] == 0) {
                unset($result[$campaign]);
                continue;
            }

            $total_talk_time += $rec['TalkTime'];

            $top_ten[$rec['Campaign']] = $rec['Calls'];

            $rec['AvgTalkTime'] = $this->secondsToHms($rec['TalkTime'] / $rec['Calls']);
            $rec['AvgHoldTime'] = $this->secondsToHms($rec['HoldTime'] / $rec['Calls']);
            $rec['AvgHandleTime'] = $this->secondsToHms(($rec['TalkTime'] + $rec['WrapUpTime']) / $rec['Calls']);
            $rec['DropRate'] = number_format($rec['Drops'] / $rec['Calls'] * 100, 2) . '%';
        }

        // sort by calls and slice top 10
        arsort($top_ten);
        $top_ten = array_slice($top_ten, 0, 10);

        // return separate arrays for each item
        return [
            'campaign_stats' => [
                'TotalTalkTime' => $this->secondsToHms($total_talk_time),
                'TopTen' => [
                    'Campaign' => array_keys($top_ten),
                    'Calls' => array_values($top_ten),
                ],
                'Campaign' => array_column($result, 'Campaign'),
                'AvgTalkTime' => array_column($result, 'AvgTalkTime'),
                'AvgHoldTime' => array_column($result, 'AvgHoldTime'),
                'AvgHandleTime' => array_column($result, 'AvgHandleTime'),
                'DropRate' => array_column($result, 'DropRate'),
            ]
        ];
    }

    private function getCampaignStats()
    {
        $activity = $this->getCampaignActivity();
        $dialingresults = $this->getCampaignDialingresults();

        // combine two results
        $final = [];

        foreach ($activity as $rec) {
            $final[$rec['Campaign']]['Campaign'] = $rec['Campaign'];
            $final[$rec['Campaign']]['Calls'] = $rec['Calls'];
            $final[$rec['Campaign']]['TalkTime'] = $rec['TalkTime'];
            $final[$rec['Campaign']]['WrapUpTime'] = $rec['WrapUpTime'];
            $final[$rec['Campaign']]['HoldTime'] = 0;
            $final[$rec['Campaign']]['Drops'] = 0;
        }

        foreach ($dialingresults as $rec) {
            if (!isset($final[$rec['Campaign']])) {
                $final[$rec['Campaign']]['Campaign'] = $rec['Campaign'];
                $final[$rec['Campaign']]['Calls'] = 0;
                $final[$rec['Campaign']]['TalkTime'] = 0;
                $final[$rec['Campaign']]['WrapUpTime'] = 0;
            }
            $final[$rec['Campaign']]['HoldTime'] = $rec['HoldTime'];
            $final[$rec['Campaign']]['Drops'] = $rec['Drops'];
        }

        return $final;
    }

    private function getCampaignActivity()
    {
        $tz = Auth::user()->tz;

        $dateFilter = $this->dateFilter;
        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $fromDate = $fromDate->format('Y-m-d H:i:s');
        $toDate = $toDate->format('Y-m-d H:i:s');

        $bind = [];

        $sql = "SELECT Campaign,
        'Calls' = SUM([Calls]),
        'TalkTime' = SUM([TalkTime]),
        'WrapUpTime' = SUM([WrapUpTime])
        FROM (";

        $union = '';
        foreach ($this->databases as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $fromDate;
            $bind['todate' . $i] = $toDate;
            $bind['rep' . $i] = $this->rep;

            $sql .= " $union SELECT AA.Campaign,
            'Calls' = SUM(CASE WHEN AA.Action IN ('Call', 'ManualCall', 'InboundCall') THEN 1 ELSE 0 END),
            'TalkTime' = SUM(CASE WHEN AA.Action IN ('Call', 'ManualCall', 'InboundCall') THEN AA.Duration ELSE 0 END),
            'WrapUpTime' = SUM(CASE WHEN AA.Action = 'Disposition' THEN AA.Duration ELSE 0 END)
            FROM [$db].[dbo].[AgentActivity] AA
            WHERE AA.GroupId = :groupid$i
            AND AA.Rep = :rep$i
            AND AA.Date >= :fromdate$i
            AND AA.Date < :todate$i
            GROUP BY AA.Campaign";

            $union = 'UNION ALL';
        }
        $sql .= ") tmp
        GROUP BY Campaign";

        return $this->runSql($sql, $bind);
    }

    private function getCampaignDialingresults()
    {
        $tz = Auth::user()->tz;

        $dateFilter = $this->dateFilter;
        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $fromDate = $fromDate->format('Y-m-d H:i:s');
        $toDate = $toDate->format('Y-m-d H:i:s');

        $bind = [];

        $sql = "SELECT Campaign,
        'HoldTime' = SUM([Holdtime]),
        'Drops' = SUM([Drops])
        FROM (";

        $union = '';
        foreach ($this->databases as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $fromDate;
            $bind['todate' . $i] = $toDate;
            $bind['rep' . $i] = $this->rep;

            $sql .= " $union SELECT DR.Campaign,
	        'Holdtime' = SUM(DR.HoldTime),
			'Drops' = SUM(CASE WHEN DR.CallStatus = 'CR_HANGUP' THEN 1 ELSE 0 END)
            FROM [$db].[dbo].[DialingResults] DR
            WHERE DR.Duration > 0
            AND DR.CallType NOT IN (7,8)
            AND DR.CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound','Inbound Voicemail','TRANSFERRED','PARKED','SMS Received','SMS Delivered')
            AND DR.GroupId = :groupid$i
            AND DR.Rep = :rep$i
            AND DR.Date >= :fromdate$i
			AND DR.Date < :todate$i
            GROUP BY Campaign";

            $union = 'UNION ALL';
        }
        $sql .= ") tmp
        GROUP BY Campaign";

        return $this->runSql($sql, $bind);
    }

    public function sales(Request $request)
    {
        $this->getSession($request);

        $result = $this->getSales();

        return ['total_sales' => $result['Sales']];
    }

    public function getSales()
    {
        $dateFilter = $this->dateFilter;
        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $fromDate = $fromDate->format('Y-m-d H:i:s');
        $toDate = $toDate->format('Y-m-d H:i:s');

        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        $bind = [];

        $sql = "SELECT SUM(Sales) as Sales
        FROM (";

        $union = '';
        foreach ($this->databases as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $fromDate;
            $bind['todate' . $i] = $toDate;
            $bind['rep' . $i] = $this->rep;

            $sql .= " $union SELECT 'Sales' = COUNT(id)
                FROM [$db].[dbo].[DialingResults] DR
                CROSS APPLY (SELECT TOP 1 [Type]
                    FROM  [$db].[dbo].[Dispos] DI
                    WHERE Disposition = DR.CallStatus
                    AND (GroupId = DR.GroupId OR IsSystem=1)
                    AND (Campaign = DR.Campaign OR Campaign = '')
                    ORDER BY [id]) DI
                WHERE DR.GroupId = :groupid$i
                AND DR.Rep = :rep$i
                AND DR.Date >= :fromdate$i
                AND DR.Date < :todate$i
                AND DR.CallType IN (1,11)
                AND DI.Type = 3";

            $union = 'UNION ALL';
        }
        $sql .= ") tmp";

        $result = $this->runSql($sql, $bind);
        return $result[0];
    }
}
