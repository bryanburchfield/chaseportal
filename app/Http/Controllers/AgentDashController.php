<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\DashTraits;
use Illuminate\Support\Carbon;

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

        $campaigns = $this->agentCampaigns();

        $jsfile = [
            "agentdash.js",
            "multiselect_lib.js"
        ];

        $cssfile[] = "agentdash.css";

        $data = [
            'isApi' => $this->isApi,
            'campaign' => $this->campaign,
            'dateFilter' => $this->dateFilter,
            'campaign_list' => $campaigns,
            'curdash' => 'agentdash',
            'jsfile' => $jsfile,
            'cssfile' => $cssfile,
        ];

        return view('agentdash')->with($data);
    }

    public function agentCampaignSearch(Request $request)
    {
        return ['search_result' => $this->agentCampaigns(trim($request->get('query')))];
    }

    public function agentUpdateFilters(Request $request)
    {
        $filters = [
            'databases',
            'campaign',
            'dateFilter',
        ];

        foreach ($filters as $filter) {
            if (isset($request->$filter)) {
                $val = $request->input($filter);
                if (is_array($val)) {
                    $val = array_filter($val);
                }
                session([$filter => $val]);
            }
        }

        Auth::user()->persistFilters($request);

        return ['campaigns' => $this->agentCampaigns()];
    }

    private function agentCampaigns($partial = null)
    {
        $request = new Request();

        $this->getSession($request);

        list($fromDate, $toDate) = $this->dateRange($this->dateFilter);

        // convert to datetime strings
        $fromDate = $fromDate->format('Y-m-d H:i:s');
        $toDate = $toDate->format('Y-m-d H:i:s');

        $bind = [
            'groupid' => Auth::user()->group_id,
            'rep' => $this->rep,
        ];

        // Find if the rep has a skill
        $sql = "SELECT Skill FROM Reps
            WHERE GroupId = :groupid
            AND RepName = :rep";

        $results = $this->runSql($sql, $bind);

        if (!count($results)) {
            $skill = null;
        } else {
            $skill = $results[0]['Skill'];
        }

        // If the rep has a skill, then create campaign list based on that
        // otherwise, get a list of all campaigns in DialingResults tagged to them
        if ($skill === null) {
            $bind['fromdate'] = $fromDate;
            $bind['todate'] = $toDate;

            $sql = "SELECT DISTINCT Campaign
                FROM DialingResults
                WHERE GroupId = :groupid
                AND Campaign != ''
                AND Rep = :rep
                AND Date >= :fromdate
                AND Date < :todate";

            if (!empty($partial)) {
                $bind['name'] = $partial . '%';
                $sql .= " AND Campaign LIKE :name";
            }
        } else {
            $sql = "SELECT C.CampaignName as Campaign
            FROM Reps R
            INNER JOIN SkillList SL ON SL.Skill = R.Skill AND SL.GroupId = R.GroupId 
            INNER JOIN Campaigns C ON C.GroupId = SL.GroupId AND C.CampaignName = SL.Campaign AND C.IsActive = 1
            WHERE R.GroupId = :groupid
            AND R.RepName = :rep";

            if (!empty($partial)) {
                $bind['name'] = $partial . '%';
                $sql .= " AND CampaignName LIKE :name";
            }
        }

        $result = $this->runSql($sql, $bind);

        $result = array_column($result, 'Campaign');

        if (empty($this->campaign)) {
            $selected = [];
        } else {
            $selected = (array) $this->campaign;
        }

        // add any selected camps that aren't in the result set
        foreach ($selected as $camp) {
            if (!in_array($camp, $result)) {
                $result[] = $camp;
            }
        }

        natcasesort($result);

        $camparray = [];

        $camparray[] = [
            'name' => trans('general.all_campaigns'),
            'value' => '',
            'selected' => empty($selected) ? 1 : 0,
        ];

        foreach ($result as $camp) {
            $camparray[] = [
                'name' => $camp,
                'value' => $camp,
                'selected' => in_array($camp, $selected) ? 1 : 0,
            ];
        }

        return $camparray;
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

        $details = $this->filterDetails($this->dateFilter);

        $tot_inbound = 0;
        $tot_talk_time = 0;
        $avg_handle_time = 0;

        $result = $this->getCallVolume();

        if (count($result)) {
            if ($result[0]['InboundCalls'] !== null) {
                $tot_inbound = $result[0]['InboundCalls'];
            }
            if ($result[0]['HandleTime'] !== null && $result[0]['HandledCalls'] !== null) {
                $avg_handle_time = ($result[0]['HandledCalls'] == 0) ? 0 : $result[0]['HandleTime'] / $result[0]['HandledCalls'];
            }
        }

        // Now get talk time
        $result = $this->getAgentTalkTime();

        if (count($result)) {
            if ($result[0]['TalkTime'] !== null) {
                $tot_talk_time = $result[0]['TalkTime'];
            }
        }

        return ['call_volume' => [
            'tot_inbound' => $tot_inbound,
            'tot_talk_time' => $this->secondsToHms($tot_talk_time),
            'avg_handle_time' => $this->secondsToHms($avg_handle_time),
            'details' => $details,
        ]];
    }

    /**
     * Query call volume - This is specific to the selected agent / campaign(s)
     *
     * @param boolean $prev
     * @return void
     */
    private function getCallVolume()
    {
        $dateFilter = $this->dateFilter;
        $campaign = $this->campaign;

        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $fromDate = $fromDate->format('Y-m-d H:i:s');
        $toDate = $toDate->format('Y-m-d H:i:s');

        $bind = [
            'groupid' => Auth::user()->group_id,
            'rep' => $this->rep,
            'fromdate' => $fromDate,
            'todate' => $toDate,
        ];

        $sql = "SELECT
            'InboundCalls' = COUNT(*),
            'HandledCalls' = SUM(CASE WHEN DR.CallStatus NOT IN ( 'CR_CEPT', 'CR_CNCT/CON_PAMD',
                'CR_NOANS', 'CR_NORB', 'CR_BUSY', 'CR_DROPPED', 'CR_FAXTONE', 'CR_FAILED', 'CR_DISCONNECTED',
                'CR_HANGUP', 'Inbound Voicemail') THEN 1 ELSE 0 END),
            'HandleTime' = SUM(CASE WHEN DR.CallStatus NOT IN ( 'CR_CEPT', 'CR_CNCT/CON_PAMD',
                'CR_NOANS', 'CR_NORB', 'CR_BUSY', 'CR_DROPPED', 'CR_FAXTONE', 'CR_FAILED', 'CR_DISCONNECTED',
                'CR_HANGUP', 'Inbound Voicemail') THEN DR.HandleTime ELSE 0 END)
            FROM DialingResults DR
            WHERE DR.CallType IN (1,11)
            AND DR.CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound')
            AND Duration > 0
            AND DR.Rep = :rep
            AND DR.Date >= :fromdate
            AND DR.Date < :todate
            AND DR.GroupId = :groupid";

        list($where, $extrabind) = $this->campaignClause('DR', 0, $campaign);
        $sql .= " $where";
        $bind = array_merge($bind, $extrabind);

        return $this->runSql($sql, $bind);
    }

    private function getAgentTalkTime()
    {
        $dateFilter = $this->dateFilter;
        $campaign = $this->campaign;

        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $fromDate = $fromDate->format('Y-m-d H:i:s');
        $toDate = $toDate->format('Y-m-d H:i:s');

        $bind = [
            'groupid' => Auth::user()->group_id,
            'rep' => $this->rep,
            'fromdate' => $fromDate,
            'todate' => $toDate,
        ];

        $sql = "SELECT 'TalkTime' = SUM(AA.Duration)
            FROM AgentActivity AA
            WHERE AA.GroupId = :groupid
            AND AA.Rep = :rep
            AND AA.Date >= :fromdate
            AND AA.Date < :todate
            AND AA.Action = 'InboundCall'";

        list($where, $extrabind) = $this->campaignClause('AA', 0, $campaign);
        $sql .= " $where";
        $bind = array_merge($bind, $extrabind);

        return $this->runSql($sql, $bind);
    }

    public function campaignStats(Request $request)
    {
        $this->getSession($request);

        $result = $this->getCampaignStats();

        // sort by campaign
        ksort($result, SORT_NATURAL | SORT_FLAG_CASE);

        $total_calls = 0;
        $total_talk_time = 0;
        $calls_by_campaign = [];

        // Compute averages
        foreach ($result as $campaign => &$rec) {
            $total_talk_time += $rec['TalkTime'];

            $calls_by_campaign[$rec['Campaign']] = $rec['Dials'];
            $total_calls += $rec['Dials'];

            $rec['AvgTalkTime'] = $this->secondsToHms(($rec['AgentCalls'] == 0) ? 0 : $rec['TalkTime'] / $rec['AgentCalls']);
            $rec['AvgHandleTime'] = $this->secondsToHms(($rec['AgentCalls'] == 0) ? 0 : ($rec['TalkTime'] + $rec['WrapUpTime']) / $rec['AgentCalls']);
            $rec['AvgHoldTime'] = $this->secondsToHms(($rec['Dials'] == 0) ? 0 : $rec['HoldTime'] / $rec['Dials']);
            $rec['DropRate'] = number_format(($rec['Dials'] == 0) ? 0 : $rec['Drops'] / $rec['Dials'] * 100, 2) . '%';
        }

        // sort by calls 
        arsort($calls_by_campaign);

        // and slice top 10
        $top_ten = array_slice($calls_by_campaign, 0, 10);

        // return separate arrays for each item
        return [
            'campaign_stats' => [
                'TotalCalls' => $total_calls,
                'TotalTalkTime' => $this->secondsToHms($total_talk_time),
                'CallsByCampaign' => [
                    'Campaign' => array_keys($calls_by_campaign),
                    'Calls' => array_values($calls_by_campaign),
                ],
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
        // get inbound calls, talk time, wrap up time
        $activity = $this->getCampaignActivity();

        // get inbound holdtime and drops
        $dialingresults = $this->getCampaignDialingresults();

        // combine two results
        $final = [];

        foreach ($activity as $rec) {
            $final[$rec['Campaign']]['Campaign'] = $rec['Campaign'];
            $final[$rec['Campaign']]['AgentCalls'] = $rec['AgentCalls'];
            $final[$rec['Campaign']]['TalkTime'] = $rec['TalkTime'];
            $final[$rec['Campaign']]['WrapUpTime'] = $rec['WrapUpTime'];
            $final[$rec['Campaign']]['HoldTime'] = 0;
            $final[$rec['Campaign']]['Drops'] = 0;
            $final[$rec['Campaign']]['Dials'] = 0;
        }

        foreach ($dialingresults as $rec) {
            if (!isset($final[$rec['Campaign']])) {
                $final[$rec['Campaign']]['Campaign'] = $rec['Campaign'];
                $final[$rec['Campaign']]['AgentCalls'] = 0;
                $final[$rec['Campaign']]['TalkTime'] = 0;
                $final[$rec['Campaign']]['WrapUpTime'] = 0;
            }
            $final[$rec['Campaign']]['Dials'] = $rec['Dials'];
            $final[$rec['Campaign']]['HoldTime'] = $rec['HoldTime'];
            $final[$rec['Campaign']]['Drops'] = $rec['Drops'];
        }

        return $final;
    }

    private function getCampaignActivity()
    {
        $dateFilter = $this->dateFilter;
        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $fromDate = $fromDate->format('Y-m-d H:i:s');
        $toDate = $toDate->format('Y-m-d H:i:s');

        $bind = [
            'groupid' => Auth::user()->group_id,
            'fromdate' => $fromDate,
            'todate' => $toDate,
        ];

        // we have to cursor through every record to link dispo times to the calls
        $sql = "SELECT Rep, Date, Campaign, Action, Duration
                FROM AgentActivity
                WHERE GroupId = :groupid
                AND Date >= :fromdate
                AND Date < :todate
                ORDER BY Rep, Date";

        // this will hold all the stats per campaign
        $campaign_stats = [];
        // this is set to true after an inbound call so we can look for dispo recs
        $aftercall = false;
        $campaign = '';

        foreach ($this->yieldSql($sql, $bind) as $rec) {
            if ($aftercall) {
                if ($rec['Action'] == 'Disposition') {
                    $campaign_stats[$campaign]['WrapUpTime'] += $rec['Duration'];
                } else {
                    $aftercall = false;
                }
            }
            if (!$aftercall) {
                if ($rec['Action'] == 'InboundCall') {
                    $campaign = $rec['Campaign'];
                    if (!isset($campaign_stats[$campaign])) {
                        $campaign_stats[$campaign]['Campaign'] = $campaign;
                        $campaign_stats[$campaign]['AgentCalls'] = 0;
                        $campaign_stats[$campaign]['TalkTime'] = 0;
                        $campaign_stats[$campaign]['WrapUpTime'] = 0;
                    }
                    $campaign_stats[$campaign]['AgentCalls']++;
                    $campaign_stats[$campaign]['TalkTime'] += $rec['Duration'];
                    $aftercall = true;
                }
            }
        }

        return $campaign_stats;
    }

    private function getCampaignDialingresults()
    {
        $dateFilter = $this->dateFilter;
        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $fromDate = $fromDate->format('Y-m-d H:i:s');
        $toDate = $toDate->format('Y-m-d H:i:s');

        $bind = [
            'groupid' => Auth::user()->group_id,
            'fromdate' => $fromDate,
            'todate' => $toDate,
        ];

        $sql = "SELECT Campaign,
                'Dials' = COUNT(*),
                'HoldTime' = SUM(HoldTime),
                'Drops' = SUM(CASE WHEN CallStatus = 'CR_HANGUP' THEN 1 ELSE 0 END)
            FROM DialingResults
            WHERE CallType IN (1,11)
            AND CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound','Inbound Voicemail','TRANSFERRED','PARKED','SMS Received','SMS Delivered')
            AND GroupId = :groupid
            AND Date >= :fromdate
			AND Date < :todate
            AND Duration > 0
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

            $sql .= " $union SELECT 'Sales' = COUNT(id)
                FROM [$db].[dbo].[DialingResults] DR
                CROSS APPLY (SELECT TOP 1 [Type]
                    FROM  [$db].[dbo].[Dispos] DI
                    WHERE Disposition = DR.CallStatus
                    AND (GroupId = DR.GroupId OR IsSystem=1)
                    AND (Campaign = DR.Campaign OR Campaign = '')
                    ORDER BY [id]) DI
                WHERE DR.GroupId = :groupid$i
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

    public function campaignChart(Request $request)
    {
        $this->getSession($request);

        $result = $this->getCampaignChart();

        $times = [];
        $campaign_calls = [];

        if (count($result)) {
            // build time array from the first camp
            $campaigns = array_keys($result);
            foreach ($result[$campaigns[0]] as $rec) {

                if ($this->byHour($this->dateFilter)) {
                    $datetime = Carbon::parse($rec['Time'])->isoFormat('H:mm');
                } else {
                    $datetime = Carbon::parse($rec['Time'])->isoFormat('ddd l');
                }

                $times[] = $datetime;
            }
            // and buld the calls over time array
            foreach ($result as $campaign => $details) {
                $campaign_calls[] = [
                    'campaign' => $campaign,
                    'calls' => array_column($details, 'Calls'),
                ];
            }
        }

        return ['campaign_chart' => [
            'times' => $times,
            'campaign_calls' => $campaign_calls,
        ]];
    }

    public function getCampaignChart()
    {
        $dateFilter = $this->dateFilter;
        $timeZoneName = Auth::user()->tz;

        list($fromDate, $toDate) = $this->dateRange($dateFilter);

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
        $fromDate = $fromDate->format('Y-m-d H:i:s');
        $toDate = $toDate->format('Y-m-d H:i:s');

        $bind = [];

        $sql = "SELECT Campaign, [Time],'Calls' = SUM([Calls])
        FROM (";

        $union = '';
        foreach ($this->databases as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $fromDate;
            $bind['todate' . $i] = $toDate;

            $sql .= " $union SELECT DR.Campaign, $xAxis Time,
			'Calls' = COUNT(*)
            FROM [$db].[dbo].[DialingResults] DR
            WHERE DR.CallType IN (1,11)
            AND DR.CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound','Inbound Voicemail','TRANSFERRED','PARKED','SMS Received','SMS Delivered')
            AND DR.GroupId = :groupid$i
            AND DR.Date >= :fromdate$i
            AND DR.Date < :todate$i
            AND DR.Duration > 0
            GROUP BY DR.Campaign, $xAxis";

            $union = 'UNION ALL';
        }
        $sql .= ") tmp
        GROUP BY Campaign, [Time]
        ORDER BY Campaign, [Time]";

        $result = $this->runSql($sql, $bind);

        // build out arrays for each campaign
        $params = [
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'modifier' => $modifier,
            'byHour' => $byHour,
            'format' => $format,
            'zeroRec' => [
                'Time' => '',
                'Calls' => 0,
            ],
        ];

        $campaign = '';
        $final = [];
        $tmp = [];

        foreach ($result as $rec) {
            if ($campaign != '' && $rec['Campaign'] != $campaign) {
                $final[$campaign] = array_map(array(&$this, $mapFunction), $this->formatVolume($tmp, $params));
                $tmp = [];
            }
            $tmp[] = [
                'Time' => $rec['Time'],
                'Calls' => $rec['Calls'],
            ];
            $campaign = $rec['Campaign'];
        }
        if (count($tmp)) {
            $final[$campaign] = array_map(array(&$this, $mapFunction), $this->formatVolume($tmp, $params));
        }

        return $final;
    }
}
