<?php

namespace App\Http\Controllers;

use App\Traits\DashTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AdminDistinctAgentDashController extends Controller
{
    use DashTraits;

    public function index(Request $request)
    {
        $this->getSession($request);

        $campaigns = $this->campaignGroups();

        $jsfile[] = "admindistinctagentdash.js";
        $cssfile[] = "admindistinctagentdash.css";

        $data = [
            'isApi' => $this->isApi,
            'campaign' => $this->campaign,
            'dateFilter' => $this->dateFilter,
            'inorout' => $this->inorout,
            'campaign_list' => $campaigns,
            'curdash' => 'admindistinctagentdash',
            'jsfile' => $jsfile,
            'cssfile' => $cssfile,
        ];

        return view('admindistinctagentdash')->with($data);
    }

    public function callVolume(Request $request)
    {
        $this->getSession($request);

        $tz = Auth::user()->ianaTz;

        $details = $this->filterDetails();

        $results = $this->getCallVolume();

        // Initialize return vars
        $actions = [];
        $campaigns = [];
        $dates = [];

        $campaign_dtl = [];
        $date_dtl = [];
        $rep_dtl = [];

        // Loop thru results, buuld return vals
        foreach ($results as $rec) {
            $actions[] = [
                'Date' =>  Carbon::parse($rec['Date'])
                    ->tz($tz)
                    ->isoFormat('L LTS'),
                'Rep' => $rec['Rep'],
                'Action' => $rec['Action'],
            ];

            // Distinct reps
            if (!isset($rep_dtl[$rec['Rep']])) {
                $rep_dtl[$rec['Rep']] = 1;
            }

            // Distinct reps per campaign
            if (!isset($campaign_dtl[$rec['Campaign']][$rec['Rep']])) {
                $campaign_dtl[$rec['Campaign']][$rec['Rep']] = 1;
            }

            // Distinct logins per day
            if ($rec['Action'] == 'Login') {
                $date = Carbon::parse($rec['Date'])
                    ->tz($tz)
                    ->isoFormat('MMM DD');

                if (!isset($date_dtl[$date][$rec['Rep']])) {
                    $date_dtl[$date][$rec['Rep']] = 1;
                }
            }
        }

        // Count disticnt reps
        foreach ($campaign_dtl as $k => $campaign) {
            $campaigns[$k] = count($campaign);
        }
        foreach ($date_dtl as $k => $date) {
            $dates[$k] = count($date);
        }

        // Sort (dates should already be sorted)
        arsort($campaigns, SORT_NUMERIC);

        $avg_reps = count($dates) ? array_sum($dates) / count($dates) : 0;

        return ['call_volume' => [
            'actions' => $actions,
            'campaigns' => $campaigns,
            'dates' => $dates,
            'rep_count' => count($rep_dtl),
            'avg_reps' => round($avg_reps, 2),
            'details' => $details,
        ]];
    }

    private function getCallVolume()
    {
        $campaign = $this->campaign;
        $dateFilter = $this->dateFilter;

        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $sql = "SELECT Date, Rep, Campaign, Action
            FROM AgentActivity AA
            WHERE GroupId = :groupid
            AND Date >= :fromdate
            AND Date < :todate
            AND Action IN ('Login','Logout','InboundCall')
            ORDER BY Date";

        $bind = [
            'groupid' => Auth::user()->group_id,
            'fromdate' => $startDate,
            'todate' => $endDate,
        ];

        list($where, $extrabind) = $this->campaignClause('DR', 0, $campaign);
        $sql .= " $where";
        $bind = array_merge($bind, $extrabind);

        return $this->runSql($sql, $bind);
    }
}
