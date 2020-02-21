<?php

namespace App\Http\Controllers;

use App\Traits\DashTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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

        // Initialize return vars
        $actions = [];
        $campaigns = [];
        $dates = [];

        $campaign_dtl = [];
        $date_dtl = [];
        $rep_dtl = [];

        foreach ($this->getCallVolume() as $rec) {

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
                    ->format('m/d/Y');

                if (!isset($date_dtl[$date][$rec['Rep']])) {
                    $date_dtl[$date][$rec['Rep']] = 1;
                }
            }
        }

        // Count distinct reps
        foreach ($campaign_dtl as $k => $campaign) {
            $campaigns[$k] = count($campaign);
        }
        foreach ($date_dtl as $k => $date) {
            $dates[$k] = count($date);
        }

        // Calc overall avg reps
        $avg_reps = count($dates) ? array_sum($dates) / count($dates) : 0;

        // convert dates array to nested indexed
        $labels = [];
        $fulldates = [];
        $counts = [];
        foreach ($dates as $date => $count) {
            $labels[] = Carbon::parse($date)->isoFormat('MMM DD');
            $fulldates[] = $date;
            $counts[] = $count;
        }
        $dates = [
            'labels' => $labels,
            'fulldates' => $fulldates,
            'counts' => $counts,
        ];

        // Sort campaigns
        arsort($campaigns, SORT_NUMERIC);

        return ['call_volume' => [
            'actions' => $actions,
            'campaigns' => $campaigns,
            'dates' => $dates,
            'rep_count' => count($rep_dtl),
            'avg_reps' => round($avg_reps, 2),
            'details' => $details,
        ]];
    }

    private function getCallVolume($date = null)
    {
        $campaign = $this->campaign;

        if ($date === null) {
            $dateFilter = $this->dateFilter;
        } else {
            $dateFilter = "$date $date";
        }

        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $bind = [
            'groupid' => Auth::user()->group_id,
            'fromdate' => $startDate,
            'todate' => $endDate,
        ];

        $sql = "SELECT Date, Rep, Campaign, Action
            FROM AgentActivity AA
            WHERE GroupId = :groupid
            AND Date >= :fromdate
            AND Date < :todate
            AND Action IN ('Login','Logout','InboundCall')";

        list($where, $extrabind) = $this->campaignClause('AA', 0, $campaign);
        $sql .= " $where";
        $bind = array_merge($bind, $extrabind);

        $sql .= " ORDER BY Date";

        return $this->yieldSql($sql, $bind);
    }

    public function getLoginDetails(Request $request)
    {
        // make sure we got a date
        if ($request->missing('date')) {
            return 'error';
        }

        $this->getSession($request);

        $tz = Auth::user()->ianaTz;

        $date_dtl = [];
        $dates = [];

        foreach ($this->getCallVolume($request->date) as $rec) {
            // Distinct logins per day
            if ($rec['Action'] == 'Login') {
                $date = Carbon::parse($rec['Date'])
                    ->tz($tz)
                    ->format('m/d/Y H:00');

                if (!isset($date_dtl[$date][$rec['Rep']])) {
                    $date_dtl[$date][$rec['Rep']] = 1;
                }
            }
        }

        // Count distinct reps
        foreach ($date_dtl as $k => $date) {
            $dates[$k] = count($date);
        }

        // Create return arrays
        foreach ($dates as $date => $count) {
            $labels[] = Carbon::parse($date)->isoFormat('ha');
            $counts[] = $count;
        }
        $dates = [
            'labels' => $labels,
            'counts' => $counts,
        ];

        return [
            'dates' => $dates,
        ];
    }
}
