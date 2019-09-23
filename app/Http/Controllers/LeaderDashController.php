<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\DashTraits;
use Illuminate\Support\Facades\Log;

class LeaderDashController extends Controller
{
    use DashTraits;

    public function index(Request $request)
    {
        $this->getSession($request);

        $campaigns = $this->campaignGroups();

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
        $details = $this->filterDetails($dateFilter, $campaign);

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

            if ($this->byHour($this->dateFilter)) {
                $datetime = date("g:i", strtotime($r['Time']));
            } else {
                $datetime = date("D n/j/y", strtotime($r['Time']));
            }

            array_push($time_labels, $datetime);
            array_push($inbound, $r['Inbound']);
            array_push($outbound, $r['Outbound']);
            array_push($manual, $r['Manual']);
        }

        return ['call_volume' => [
            'time_labels' => $time_labels,
            'inbound' => $inbound,
            'outbound' => $outbound,
            'manual' => $manual,
            'tot_outbound' => $tot_outbound,
            'tot_inbound' => $tot_inbound,
            'details' => $details,
        ]];
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

        $bind = [];

        $sql = "SELECT Time,
		'Outbound' = SUM([Outbound]),
		'Inbound' = SUM([Inbound]),
		'Manual' = SUM([Manual])
		FROM (";
        $union = '';
        foreach ($this->databases as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $startDate;
            $bind['todate' . $i] = $endDate;

            $sql .= " $union SELECT $xAxis as 'Time',
            'Outbound' = CASE WHEN DR.CallType NOT IN (1,2,11) THEN 1 ELSE 0 END,
            'Inbound' = CASE WHEN DR.CallType IN (1,11) THEN 1 ELSE 0 END,
            'Manual' = CASE WHEN DR.CallType IN (2) THEN 1 ELSE 0 END
			FROM [$db].[dbo].[DialingResults] DR
            WHERE DR.CallType NOT IN (7,8)
            AND DR.Duration > 0
			AND DR.CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound','TRANSFERRED','PARKED','SMS Received','SMS Delivered')
			AND DR.GroupId = :groupid$i
			AND DR.Date >= :fromdate$i
            AND DR.Date < :todate$i";

            list($where, $extrabind) = $this->campaignClause('DR', $i, $campaign);
            $sql .= " $where";
            $bind = array_merge($bind, $extrabind);

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

        $bind = [];

        $sql = "SELECT
		Rep,
		'CallCount' = SUM(Cnt),
		'TalkSecs' = SUM(Duration),
		'Sales' = SUM(Sales)
		FROM (";
        $union = '';
        foreach ($this->databases as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $startDate;
            $bind['todate' . $i] = $endDate;

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
			WHERE DR.GroupId = :groupid$i
			AND DR.Rep != ''
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
			GROUP BY DR.Rep";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp
		GROUP BY Rep
		ORDER BY 'Sales' DESC, Rep";

        $result = $this->runSql($sql, $bind);

        $repsales = [];
        $tots = [
            'Rep' => 'ROOM TOTAL',
            'CallCount' => 0,
            'TalkSecs' => 0,
            'Sales' => 0,
        ];

        $i = 0;
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
            $i++;
        }

        $tots['TalkSecs'] = secondsToHms($tots['TalkSecs']);

        // Top 20 for the leaderboard
        $result = array_slice($result, 0, 20);
        $result[] = $tots;

        // sort and top 10 the per hour recs
        usort($repsales, function ($a, $b) {
            return $b['PerHour'] <=> $a['PerHour'];
        });

        $repsales = array_slice($repsales, 0, 10);

        return [
            'call_details' => [
                'leaders' => $result,
                'repsales' => $repsales,
            ],
            'Rep' => array_column($repsales, 'Rep'),
            'Sales' => array_column($repsales, 'Sales'),
        ];
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

        $bind = [];

        $sql = "SELECT TOP 10
        Campaign,
        'Sales' = SUM(Sales)
        FROM (";
        $union = '';
        foreach ($this->databases as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['fromdate' . $i] = $startDate;
            $bind['todate' . $i] = $endDate;

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
            WHERE DR.GroupId = :groupid$i
            AND DR.CallType NOT IN (1,7,8,11)
            AND DR.Campaign != ''
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
            GROUP BY DR.Campaign";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp
        GROUP BY Campaign
        HAVING SUM(Sales) > 0
        ORDER BY Sales DESC";

        $result = $this->runSql($sql, $bind);

        return [
            'Campaign' => array_column($result, 'Campaign'),
            'Sales' => array_column($result, 'Sales'),
        ];
    }
}
