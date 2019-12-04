<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\MessageBag;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait ReportTraits
{
    public $errors;
    public $params;
    public $extras;

    use ReportExportTraits;
    use SqlServerTraits;
    use TimeTraits;

    private function initilaizeParams()
    {
        $this->params = [
            'report' => Str::snake((new \ReflectionClass($this))->getShortName()),
            'fromdate' => '',
            'todate' => '',
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

    public function setDates()
    {
        if (!empty($this->params['datesOptional'])) {
            $this->params['fromdate'] = '';
            $this->params['todate'] = '';
        } else {
            $this->params['fromdate'] = Carbon::parse('today 09:00')->isoFormat('L LT');
            $this->params['todate'] = Carbon::parse('today 20:00')->isoFormat('L LT');
        }
    }

    /**
     * Set Headings
     *
     * translates some parameters
     * Can't do this in __construct()
     *
     * @return void
     */
    private function setHeadings($hasdates = true)
    {
        $this->params['reportName'] = trans($this->params['reportName']);

        foreach ($this->params['columns'] as &$col) {
            $col = trans($col);
        }
    }

    public function getAllInboundSources()
    {
        $sql = '';
        $union = '';
        foreach (Auth::user()->getDatabaseList() as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;

            $sql .= "$union SELECT InboundSource, Description
            FROM [$db].[dbo].[InboundSources]
            WHERE GroupId = :groupid$i
            AND InboundSource != ''";

            $union = ' UNION';
        }
        $sql .= " ORDER BY Description, InboundSource";

        $results = resultsToList($this->runSql($sql, $bind));

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

        $results = resultsToList($this->runSql($sql, $bind));

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

        $results = resultsToList($this->runSql($sql, $bind));

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

        $results = resultsToList($this->runSql($sql, $bind));

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
        if ($start == '' || $end == '') {
            return [null, null];
        }

        $tz = Auth::user()->iana_tz;

        $fromDate = $this->localToUtc($start, $tz);
        $toDate = $this->localToUtc($end, $tz);

        return [$fromDate, $toDate];
    }

    private function checkPageFilters(Request $request)
    {
        $this->errors = new MessageBag();

        if (!empty($request->databases)) {
            if (count($request->databases) == 0) {
                $this->errors->add('databases', trans('reports.errdatabases'));
            }
            $this->params['databases'] = $request->databases;
        } else {
            $this->params['databases'] = Auth::user()->getDatabaseList();
        }

        if (!empty($request->th_sort)) {
            $this->setHeadings();
            $col = array_search($request->th_sort, $this->params['columns']);
            $dir = $request->sort_direction ?? 'asc';
            $this->params['orderby'] = [$col => $dir];
        }

        if (!empty($request->curpage)) {
            if ($request->curpage <= 0) {
                $this->errors->add('pagenumb', trans('reports.errpagenumb'));
            }
            $this->params['curpage'] = $request->curpage;
        }

        if (!empty($request->pagesize)) {
            if ($request->pagesize <= 0) {
                $this->errors->add('pagesize', trans('reports.errpagesize'));
            }
            $this->params['pagesize'] = $request->pagesize;
        }
    }

    private function checkDateRangeFilters(Request $request)
    {
        $from = null;
        $to = null;

        if (empty($request->input('fromdate'))) {
            if (!$this->params['datesOptional']) {
                $this->errors->add('fromdate.required', trans('reports.errfromdaterequired'));
            }
        } else {
            try {
                if ($request->export) {
                    $from = Carbon::parse($request->input('fromdate'));
                } else {
                    $from = Carbon::createFromIsoFormat('L LT', $request->input('fromdate'), null, App::getLocale());
                }
            } catch (Exception $e) {
                $from = false;
                $this->errors->add('fromdate.invalid', trans('reports.errfromdateinvalid'));
            }
        }

        if (empty($request->input('todate'))) {
            if (!$this->params['datesOptional']) {
                $this->errors->add('todate.required', trans('reports.errtodaterequired'));
            }
        } else {
            try {
                if ($request->export) {
                    $to = Carbon::parse($request->input('todate'));
                } else {
                    $to = Carbon::createFromIsoFormat('L LT', $request->input('todate'), null, App::getLocale());
                }
            } catch (Exception $e) {
                $to = false;
                $this->errors->add('todate.invalid', trans('reports.errtodateinvalid'));
            }
        }

        if (gettype($from) !== gettype($to)) {
            $this->errors->add('daterange', trans('reports.errdaterange'));
        } elseif ($to < $from) {
            $this->errors->add('daterange', trans('reports.errdaterange'));
        } elseif (gettype($from) == 'object') {
            $this->params['fromdate'] = $from->toDateTimeString();
            $this->params['todate'] = $to->toDateTimeString();
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

        if (empty($results)) {
            $this->errors = new MessageBag();
            $this->errors->add('results', trans('reports.errresults'));
            return $this->errors;
        }

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

    private function getSessionParams(Request $request)
    {
        // if we're not doing report export, return
        if (empty($request->export)) {
            return $request;
        }

        $newrequest = $request->duplicate();

        $report = (new \ReflectionClass($this))->getShortName();

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
                    $newrequest->merge([$k => $v]);
                }
            }
        }
        return $newrequest;
    }
}
