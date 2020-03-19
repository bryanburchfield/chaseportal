<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\DashTraits;
use Illuminate\Support\Facades\Log;

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
            'inorout' => $this->inorout,
            'campaign_list' => $campaigns,
            'curdash' => 'agentdash',
            'jsfile' => $jsfile,
            'cssfile' => $cssfile,
        ];

        return view('agentdash')->with($data);
    }

    public function agentCampaigns()
    {
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
            $dateFilter = $this->dateFilter;
            list($fromDate, $toDate) = $this->dateRange($dateFilter);

            // convert to datetime strings
            $fromDate = $fromDate->format('Y-m-d H:i:s');
            $toDate = $toDate->format('Y-m-d H:i:s');

            $bind['fromdate'] = $fromDate;
            $bind['todate'] = $toDate;

            $sql = "SELECT DISTINCT Campaign
                FROM DialingResults
                WHERE GroupId = :groupid
                AND Campaign != ''
                AND Rep = :rep
                AND Date >= :fromdate
                AND Date < :todate";
        } else {
            $sql = "SELECT C.CampaignName as Campaign
            FROM Reps R
            INNER JOIN SkillList SL ON SL.Skill = R.Skill AND SL.GroupId = R.GroupId 
            INNER JOIN Campaigns C ON C.GroupId = SL.GroupId AND C.CampaignName = SL.Campaign AND C.IsActive = 1
            WHERE R.GroupId = :groupid
            AND R.RepName = :rep";
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

        $tot_outbound = 0;
        $tot_inbound = 0;
        $avg_handle_time = 0;

        $result = $this->getCallVolume();

        if (count($result)) {
            $tot_outbound = $result[0]['OutboundCalls'];
            $tot_inbound = $result[0]['InboundCalls'];
            $avg_handle_time = $result[0]['HandledCalls'] == 0 ? 0 : $result[0]['HandleTime'] / $result[0]['HandledCalls'];
        }

        $avg_handle_time = $this->secondsToHms($avg_handle_time);
        Log::debug(
            ['call_volume' => [
                'tot_outbound' => $tot_outbound,
                'tot_inbound' => $tot_inbound,
                'avg_handle_time' => $avg_handle_time,
                'details' => $details,
            ]]
        );

        return ['call_volume' => [
            'tot_outbound' => $tot_outbound,
            'tot_inbound' => $tot_inbound,
            'avg_handle_time' => $avg_handle_time,
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
            'InboundCalls' = SUM(CASE WHEN DR.CallType IN (1,11) THEN 1 ELSE 0 END),
            'OutboundCalls' = SUM(CASE WHEN DR.CallType NOT IN (1,11) THEN 1 ELSE 0 END),
            'HandledCalls' = SUM(CASE WHEN DR.CallStatus NOT IN ( 'CR_CEPT', 'CR_CNCT/CON_PAMD', 'CR_NOANS', 'CR_NORB', 'CR_BUSY',
                'CR_DROPPED', 'CR_FAXTONE', 'CR_FAILED', 'CR_DISCONNECTED',
                'CR_HANGUP', 'Inbound Voicemail') THEN 1 ELSE 0 END),
            'HandleTime' = SUM(CASE WHEN DR.CallStatus NOT IN ( 'CR_CEPT', 'CR_CNCT/CON_PAMD', 'CR_NOANS', 'CR_NORB', 'CR_BUSY',
                'CR_DROPPED', 'CR_FAXTONE', 'CR_FAILED', 'CR_DISCONNECTED',
                'CR_HANGUP', 'Inbound Voicemail') THEN DR.HandleTime ELSE 0 END)
            FROM DialingResults DR
            WHERE DR.CallType NOT IN (7,8)
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
