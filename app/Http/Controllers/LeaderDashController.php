<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Campaign;
use App\DialingResult;
use App\AgentActivity;

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
            $xAxis = "CAST(CAST(CONVERT(datetimeoffset, Date) AT TIME ZONE '$timeZoneName' AS DATE) AS DATETIME)";
        }

        $select = "'Time' = $xAxis,
        'Outbound' = SUM(CASE WHEN CallType IN ('0','4','5','6','10','12','14','15') THEN 1 ELSE 0 END),
        'Inbound' = SUM(CASE WHEN CallType IN ('1','11') THEN 1 ELSE 0 END),
        'Manual' = SUM(CASE WHEN CallType IN ('2') THEN 1 ELSE 0 END),
        'Duration' = SUM(CASE WHEN CallType IN ('1','11') THEN HandleTime ELSE 0 END)";

        $query = DialingResult::select(DB::raw($select));

        $query->where('CallType', '!=', 7)
            ->where('Duration', '>', 0)
            ->whereNotIn('CallStatus', ['CR_CNCT/CON_CAD', 'CR_CNCT/CON_PVD', 'Inbound', 'TRANSFERRED', 'PARKED', 'SMS Received', 'SMS Delivered'])
            ->where('GroupId', Auth::user()->group_id)
            ->where('Date', '>=', $startDate)
            ->where('Date', '<', $endDate);

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
            'zeroRec' => [
                'Time' => '',
                'Outbound' => 0,
                'Inbound' => 0,
                'Manual' => 0,
                'Duration' => 0,
            ],
        ];
        $result = $this->formatVolume($result, $params);

        // format the xAxis datetimes
        $result = array_map(array(&$this, $mapFunction), $result);

        // prepare json return
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

        $new_result['time_labels'] = $time_labels;
        $new_result['inbound'] = $inbound;
        $new_result['outbound'] = $outbound;
        $new_result['manual'] = $manual;
        $new_result['tot_outbound'] = $tot_outbound;
        $new_result['tot_inbound'] = $tot_inbound;

        list($campaign, $details) = $this->filterDetails();
        $new_result['campaign'] = $campaign;
        $new_result['details'] = $details;

        $return['call_volume'] = $new_result;
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
        foreach (Auth::user()->getDatabaseArray() as $db) {
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
                $bind['campaign'] = $campaign;
                $sql .= " AND DR.Campaign = :campaign";
            }
            $sql .= " GROUP BY DR.Campaign";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp
        GROUP BY Campaign
        ORDER BY Campaign";

        $return['salespercampaign'] = $this->runSql($sql, $bind);
        echo json_encode($return);
    }

    public function leaderBoard(Request $request)
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
		'Call Count' = SUM(Cnt),
		'Talk Secs' = SUM(Duration),
		'Sales' = SUM(Sales)
		FROM (";
        $union = '';
        foreach (Auth::user()->getDatabaseArray() as $db) {
            $sql .= " $union SELECT
			DR.Rep,
			'Cnt' = COUNT(DR.CallStatus),
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
			AND DR.CallStatus NOT IN (
'CR_CEPT', 'CR_CNCT/CON_PAMD', 'CR_NOANS',
'CR_NORB', 'CR_BUSY', 'CR_DROPPED', 'CR_FAXTONE',
'CR_FAILED', 'CR_DISCONNECTED', 'CR_CNCT/CON_CAD',
'CR_CNCT/CON_PVD', ' ', 'CR_HANGUP', 'Inbound')
            AND DR.Date >= :fromdate
            AND DR.Date < :todate";

            if (!empty($campaign) && $campaign != 'Total') {
                $bind['campaign'] = $campaign;
                $sql .= " AND DR.Campaign = :campaign";
            }
            $sql .= " GROUP BY DR.Rep";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp
		GROUP BY Rep
		ORDER BY 'Sales' DESC, Rep";

        $results = $this->runSql($sql, $bind);

        // process to json
        $repsales = [];
        $tots = [
            'Rep' => 'TOTAL',
            'Call Count' => 0,
            'Talk Secs' => 0,
            'Sales' => 0,
        ];
        foreach ($results as &$rec) {
            if ($rec['Sales'] > 0) {
                $repsales[] = [
                    'Rep' => $rec['Rep'],
                    'Sales' => $rec['Sales'],
                    'PerHour' => $rec['Talk Secs'] != 0 ? round($rec['Sales'] / $rec['Talk Secs'] * 3600, 2) : 0,
                ];
            }
            $tots['Talk Secs'] += $rec['Talk Secs'];
            $tots['Call Count'] += $rec['Call Count'];
            $tots['Sales'] += $rec['Sales'];
            $rec['Talk Secs'] = secondsToHms($rec['Talk Secs']);
        }
        $tots['Talk Secs'] = secondsToHms($tots['Talk Secs']);

        // Top 20
        if (count($results) > 20) {
            $results = array_slice($results, 0, 20);
        }
        $results[] = $tots;

        $return['leader_board'] = $results;
        $return['repsales'] = $repsales;
        echo json_encode($return);
    }

    public function salesPerHour()
    {
        # code...
    }
}
