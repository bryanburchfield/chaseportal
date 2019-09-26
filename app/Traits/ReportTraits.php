<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use Illuminate\Http\Request;
use \App\Traits\ReportExportTraits;

trait ReportTraits
{
    public $errors;
    public $params;
    public $extras;

    use ReportExportTraits;

    private function initilaizeParams()
    {
        $this->params = [
            'curpage' => 1,
            'pagesize' => 50,
            'totrows' => 0,
            'totpages' => 0,
            'orderby' => [],
            'groupby' => null,
            'hasTotals' => false,
            'databases' => [],
            'columns' => [],
        ];
    }

    public function getAllCampaigns($fromDate = null, $toDate = null)
    {
        $sql = '';
        $bind = [];

        if (empty($fromDate) || empty($toDate)) {
            $union = '';
            foreach (array_values(Auth::user()->getDatabaseArray()) as $i => $db) {
                $bind['groupid' . $i] = Auth::user()->group_id;

                $sql .= "$union SELECT CampaignName AS Campaign
                FROM [$db].[dbo].[Campaigns]
                WHERE isActive = 1
                AND GroupId = :groupid$i
                AND CampaignName != ''";

                $union = ' UNION';
            }
        } else {
            $fromDate = makeDateTime($fromDate);
            $toDate = makeDateTime($toDate);

            list($fromDate, $toDate) = $this->dateRange($fromDate, $toDate);

            // convert to datetime strings
            $startDate = $fromDate->format('Y-m-d H:i:s');
            $endDate = $toDate->format('Y-m-d H:i:s');

            $union = '';
            foreach (array_values(Auth::user()->getDatabaseArray()) as $i => $db) {
                $bind['groupid' . $i] = Auth::user()->group_id;
                $bind['startdate' . $i] = $startDate;
                $bind['enddate' . $i] = $endDate;

                $sql .= "$union SELECT DISTINCT Campaign
                FROM [$db].[dbo].[DialingResults]
                WHERE GroupId = :groupid$i
                AND Campaign != ''
                AND Date >= :startdate$i
                AND Date < :enddate$i";

                $union = ' UNION';
            }
        }

        $results = $this->resultsToList($this->runSql($sql, $bind));

        $results = ['_MANUAL_CALL_' => '_MANUAL_CALL_'] + $results;

        ksort($results, SORT_NATURAL);

        return $results;
    }

    public function getAllSubcampaigns($campaign = null)
    {
        if (empty($campaign)) {
            return [];
        }

        $sql = '';
        $union = '';
        foreach (array_values(Auth::user()->getDatabaseArray()) as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['campaign' . $i] = $campaign;;

            $sql .=  "$union SELECT DISTINCT Subcampaign
            FROM [$db].[dbo].[Leads]
            WHERE GroupId = :groupid$i
            AND Campaign = :campaign$i
            AND Subcampaign is not null
            AND Subcampaign != ''";

            $union = ' UNION';
        }
        $sql .= " ORDER BY Subcampaign";

        $results = $this->resultsToList($this->runSql($sql, $bind));

        return $results;
    }

    public function getAllInboundSources()
    {
        $sql = '';
        $union = '';
        foreach (Auth::user()->getDatabaseList() as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;

            $sql .= "$union SELECT InboundSource, Description
            FROM [$db].[dbo].[InboundSources]
            WHERE GroupId = :groupid$i";

            $union = ' UNION';
        }
        $sql .= " ORDER BY Description, InboundSource";

        $results = $this->resultsToList($this->runSql($sql, $bind));

        $arr = [];
        foreach ($results as $k => $v) {
            $v = $v . " ($k)";
            $arr[$k] = $v;
        }

        return $arr;
    }

    public function getAllReps($rollups = false)
    {
        $bind = [];

        $sql = '';
        $union = '';

        foreach (Auth::user()->getDatabaseList() as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;

            $sql .= " $union SELECT RepName
            FROM [$db].[dbo].[Reps]
            WHERE isActive = 1
            AND GroupId = :groupid$i";

            $union = ' UNION';
        }
        $sql .= " ORDER BY RepName";

        $results = $this->resultsToList($this->runSql($sql, $bind));

        if ($rollups) {
            array_unshift($results, '[All Unanswered]');
            array_unshift($results, '[All Answered]');
        }

        return $results;
    }

    public function getAllSkills()
    {
        $bind = [];
        $sql = '';
        $union = '';
        foreach (Auth::user()->getDatabaseList() as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;

            $sql .= " $union SELECT SkillName
            FROM [$db].[dbo].[Skills]
            WHERE GroupId = :groupid$i";

            $union = ' UNION';
        }
        $sql .= " ORDER BY SkillName";

        $results = $this->resultsToList($this->runSql($sql, $bind));

        return $results;
    }

    public function getAllCallStatuses()
    {
        $groupId = Auth::user()->group_id;
        $bind = [];

        $sql = '';
        $union = '';
        foreach (Auth::user()->getDatabaseList() as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;

            $sql .= "$union SELECT DISTINCT CallStatus
            FROM [$db].[dbo].[DialingResults]
            WHERE GroupId = :groupid$i
            AND CallStatus != ''";

            $union = ' UNION';
        }
        $sql .= " ORDER BY CallStatus";

        $results = $this->resultsToList($this->runSql($sql, $bind));

        return $results;
    }

    public function getAllCallTypes()
    {
        return [
            'Outbound' => 'Outbound',
            'Inbound' => 'Inbound',
            'Manual' => 'Manual',
            'Transferred' => 'Transferred',
            'Conference' => 'Conference',
            'Progresive' => 'Progresive',
            'TextMessage' => 'TextMessage',
        ];
    }

    private function dateRange($start, $end)
    {
        $tz = Auth::user()->getIanaTz();

        $fromDate = localToUtc($start, $tz);
        $toDate = localToUtc($end, $tz);

        return [$fromDate, $toDate];
    }

    private function runSql($sql, $bind)
    {
        $db = Auth::user()->db;
        config(['database.connections.sqlsrv.database' => $db]);

        try {
            $results = DB::connection('sqlsrv')->select(DB::raw($sql), $bind);
        } catch (\Exception $e) {
            $results = [];
        }

        if (count($results)) {
            // convert array of objects to array of arrays
            $results = json_decode(json_encode($results), true);
        }

        return $results;
    }

    private function resultsToList($results)
    {
        // flatten array, create k=>v pairs
        if (count($results)) {
            $arr = [];
            if (count($results[0]) == 1) {
                $key = implode('', array_keys($results[0]));
                $results = array_column($results, $key);
                foreach ($results as $v) {
                    $arr[$v] = $v;
                }
            } elseif (count($results[0]) == 2) {
                foreach ($results as $rec) {
                    $vals = array_values($rec);
                    $arr[$vals[0]] = $vals[1];
                }
            }
            $results = $arr;
        }
        return $results;
    }

    private function checkPageFilters(Request $request)
    {
        $this->errors = new MessageBag();

        if (!empty($request->databases)) {
            if (count($request->databases) == 0) {
                $this->errors->add('databases', "Must select at least 1 Database");
            }
            $this->params['databases'] = $request->databases;
        } else {
            $this->params['databases'] = ['defaultsomethinghere'];
        }

        if (!empty($request->th_sort)) {
            $col = array_search($request->th_sort, $this->params['columns']);
            $dir = $request->sort_direction ?? 'asc';
            $this->params['orderby'] = [$col => $dir];
        }

        if (!empty($request->curpage)) {
            if ($request->curpage <= 0) {
                $this->errors->add('pagenumb', "Invalid page number");
            }
            $this->params['curpage'] = $request->curpage;
        }

        if (!empty($request->pagesize)) {
            if ($request->pagesize <= 0) {
                $this->errors->add('pagesize', "Invalid page size");
            }
            $this->params['pagesize'] = $request->pagesize;
        }
    }

    private function checkDateRangeFilters(Request $request)
    {
        if (empty($request->fromdate)) {
            $this->errors->add('fromdate.required', "From date required");
        } else {
            $this->params['fromdate'] = $request->fromdate;
            $from = strtotime($this->params['fromdate']);

            if ($from === false) {
                $this->errors->add('fromdate.invalid', "From date not a valid date/time");
            }
        }

        if (empty($request->todate)) {
            $this->errors->add('todate.required', "To date required");
        } else {
            $this->params['todate'] = $request->todate;
            $to = strtotime($this->params['todate']);

            if ($to === false) {
                $this->errors->add('todate.invalid', "To date not a valid date/time");
            }
        }

        if (!empty($from) && !empty($to) && $to < $from) {
            $this->errors->add('daterange', "To date must be after From date");
        }
    }

    public function getResults(Request $request)
    {
        $this->processInput($request);

        if ($this->errors->isNotEmpty()) {
            return $this->errors;
        }

        $all = empty($request->all) ? false : true;

        $results = $this->executeReport($all);

        return $results;
    }

    private function getPage($results, $all = false)
    {
        if ($all) {
            return $results;
        }

        // there should be at least 2 rows, including tots
        if (!count($results)) {
            return $results;  // empty array
        }

        // remove totals row
        if ($this->params['hasTotals']) {
            $totals = $results[count($results) - 1];
            array_pop($results);
        }

        $offset = ($this->params['curpage'] - 1) * $this->params['pagesize'];
        $limit = $this->params['pagesize'];

        $page = array_slice($results, $offset, $limit);

        if ($this->params['hasTotals']) {
            $page[] = $totals;
        }

        return $page;
    }

    private function arrayData($array)
    {
        $data = [];

        foreach ($array as $rec) {
            $data[] = array_values($rec);
        }

        return $data;
    }

    private function saveSessionParams()
    {
        $report = join('', array_slice(explode('\\', get_class($this)), -1));

        foreach ($this->params as $k => $v) {
            if (
                $k != 'reportName' &&
                $k != 'curpage' &&
                $k != 'pagesize' &&
                $k != 'totrows' &&
                $k != 'totpages' &&
                $k != 'groupby' &&
                $k != 'hasTotals' &&
                $k != 'columns'
            ) {
                session([$report . "_params['$k']" => $v]);
            }
        }
    }

    private function getSessionParams($request)
    {
        // if we're not doing report export, return
        if (empty($request->export)) {
            return $request;
        }

        $newrequest = $request;

        $report = join('', array_slice(explode('\\', get_class($this)), -1));

        foreach ($this->params as $k => $v) {
            if (
                $k != 'reportName' &&
                $k != 'curpage' &&
                $k != 'pagesize' &&
                $k != 'totrows' &&
                $k != 'totpages' &&
                $k != 'groupby' &&
                $k != 'hasTotals' &&
                $k != 'columns'
            ) {
                $param = $report . "_params['$k']";
                if ($request->session()->has($param)) {
                    $v = $request->session()->get($param);
                    $newrequest->request->add([$k => $v]);
                }
            }
        }

        return $newrequest;
    }
}
