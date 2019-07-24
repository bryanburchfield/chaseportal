<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Campaign;
use App\DialingResult;

class LeaderDashController extends Controller
{
    use DashTraits;

    public function index(Request $request)
    {
        $this->getSession($request);

        $groupId = Auth::user()->group_id;
        $campaigns = Campaign::where('GroupId', $groupId)->where('IsActive', 1)->pluck('CampaignName')->toArray();
        natcasesort($campaigns);
        array_unshift($campaigns, 'Total');

        $jsfile[] = "leaderdash.js";
        $cssfile[] = "leaderdash.css";

        $data = [
            'isApi' => $this->isApi,
            'campaign' => $this->campaign,
            'datefilter' => $this->dateFilter,
            'inorout' => $this->inorout,
            'campaign_list' => $campaigns,
            'curdash' => 'leaderdash',
            'jsfile' => $jsfile,
            'cssfile' => $cssfile,
        ];
        return view('leaderdash')->with($data);
    }

    public function callVolume(Request $request)
    {
        $this->getSession($request);

        $campaign = $this->campaign;
        $dateFilter = $this->dateFilter;

        $result = $this->getCallVolume();

        $time_labels = [];
        $inbound = [];
        $outbound = [];
        $manual = [];
        $tot_inbound = 0;
        $tot_outbound = 0;

        foreach ($result as $r) {

            $tot_inbound += $r['Inbound'];
            $tot_outbound += $r['Outbound'];
            $tot_outbound += $r['Manual'];

            if (!strpos($r['Time'], ':')) {
                $datetime = date("n/j/y", strtotime($r['Time']));
            } else {
                $datetime = $r['Time'];
            }

            array_push($time_labels, $datetime);
            array_push($inbound, $r['Inbound']);
            array_push($outbound, $r['Outbound']);
            array_push($manual, $r['Manual']);
        }

        $details = $this->filterDetails($dateFilter, $campaign);

        $new_result = [
            'time_labels' => $time_labels,
            'inbound' => $inbound,
            'outbound' => $outbound,
            'manual' => $manual,
            'tot_outbound' => $tot_outbound,
            'tot_inbound' => $tot_inbound,
            'details' => $details,
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
            'groupid' => Auth::user()->group_id,
            'fromdate' => $startDate,
            'todate' => $endDate,
        ];

        $sql = "SELECT Time,
		'Outbound' = SUM([Outbound]),
		'Inbound' = SUM([Inbound]),
		'Manual' = SUM([Manual]),
		'Duration' = SUM([Duration])
		FROM (";
        $union = '';
        foreach (Auth::user()->getDatabaseArray() as $i => $db) {
            $sql .= " $union SELECT $xAxis as 'Time',
			'Outbound' = SUM(CASE WHEN DR.CallType IN ('0','4','5','6','10','12','14','15') THEN 1 ELSE 0 END),
			'Inbound' = SUM(CASE WHEN DR.CallType IN ('1','11') THEN 1 ELSE 0 END),
			'Manual' = SUM(CASE WHEN DR.CallType IN ('2') THEN 1 ELSE 0 END),
			'Duration' = SUM(CASE WHEN DR.CallType IN ('1','11') THEN DR.HandleTime ELSE 0 END)
			FROM [$db].[dbo].[DialingResults] DR
			WHERE DR.CallType NOT IN (7,8)
			AND DR.CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound','TRANSFERRED','PARKED','SMS Received','SMS Delivered')
			AND DR.GroupId = :groupid
			AND DR.Date >= :fromdate
            AND DR.Date < :todate";

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

    public function callDetails(Request $request)
    {
        $this->getSession($request);

        $campaign = $this->campaign;
        $dateFilter = $this->dateFilter;

        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $bind = [
            'groupid' => Auth::user()->group_id,
            'fromdate' => $startDate,
            'todate' => $endDate,
        ];

        $sql = "SELECT
		Rep,
		'CallCount' = SUM(Cnt),
		'TalkSecs' = SUM(Duration),
		'Sales' = SUM(Sales)
		FROM (";
        $union = '';
        foreach (Auth::user()->getDatabaseArray() as $i => $db) {
            $sql .= " $union SELECT
			DR.Rep,
			'Cnt' = COUNT(DR.CallStatus),
			'Duration' = SUM(DR.Duration),
			'Sales' = COUNT(CASE WHEN DI.Type = '3' THEN 1 ELSE NULL END)
			FROM [$db].[dbo].[DialingResults] DR
			OUTER APPLY (SELECT TOP 1 [Type]
				FROM  [$db].[dbo].[Dispos]
				WHERE Disposition = DR.CallStatus
				AND (GroupId = DR.GroupId OR IsSystem=1)
				AND (Campaign = DR.Campaign OR Campaign = '') 
				ORDER BY [Description] Desc) DI
			WHERE DR.GroupId = :groupid
			AND DR.Rep != ''
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
			GROUP BY DR.Rep";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp
		GROUP BY Rep
		ORDER BY 'Sales' DESC, Rep";

        $result = $this->runSql($sql, $bind);

        $repsales = [];
        $tots = [
            'Rep' => 'TOTAL',
            'CallCount' => 0,
            'TalkSecs' => 0,
            'Sales' => 0,
        ];

        foreach ($result as &$rec) {
            if ($rec['Sales'] > 0) {
                $repsales[] = [
                    'Rep' => $rec['Rep'],
                    'Sales' => $rec['Sales'],
                    'PerHour' => $rec['TalkSecs'] != 0 ? round($rec['Sales'] / $rec['TalkSecs'] * 3600, 2) : 0,
                ];
            }
            $tots['TalkSecs'] += $rec['TalkSecs'];
            $tots['CallCount'] += $rec['CallCount'];
            $tots['Sales'] += $rec['Sales'];
            $rec['TalkSecs'] = secondsToHms($rec['TalkSecs']);
        }
        $tots['TalkSecs'] = secondsToHms($tots['TalkSecs']);

        // Top 20
        if (count($result) > 20) {
            $results = array_slice($result, 0, 20);
        }
        $result[] = $tots;

        $return = [
            'call_details' => ['leaders' => $result, 'repsales' => $repsales],
            'Rep' => array_column($repsales, 'Rep'),
            'Sales' => array_column($repsales, 'Sales'),
        ];

        echo json_encode($return);
    }

    public function salesPerCampaign(Request $request)
    {

        $this->getSession($request);

        $campaign = $this->campaign;
        $dateFilter = $this->dateFilter;

        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $bind = [
            'groupid' => Auth::user()->group_id,
            'fromdate' => $startDate,
            'todate' => $endDate,
        ];

        $sql = "SELECT
        Campaign,
        'Sales' = SUM(Sales)
        FROM (";
        $union = '';
        foreach (Auth::user()->getDatabaseArray() as $i => $db) {
            $sql .= " $union SELECT
            DR.Campaign,
            'Sales' = COUNT(CASE WHEN DI.Type = '3' THEN 1 ELSE NULL END)
            FROM [$db].[dbo].[DialingResults] DR
            CROSS APPLY (SELECT TOP 1 [Type]
                FROM  [$db].[dbo].[Dispos]
                WHERE Disposition = DR.CallStatus
                AND (GroupId = DR.GroupId OR IsSystem=1)
                AND (Campaign = DR.Campaign OR Campaign = '') 
                ORDER BY [Description] Desc) DI
            WHERE DR.GroupId = :groupid
            AND DR.CallType NOT IN (1,7,8,11)
            AND DR.Campaign != ''
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
            GROUP BY DR.Campaign";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp
        GROUP BY Campaign
        ORDER BY Sales DESC";

        $result = $this->runSql($sql, $bind);

        $return = [
            'Campaign' => array_column($result, 'Campaign'),
            'Sales' => array_column($result, 'Sales'),
        ];

        echo json_encode($return);
    }
}
