<?php

namespace App\Services\Reports;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use Illuminate\Http\Request;

trait ReportTraits
{
    public $errors;
    public $params;

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
            'columns' => [],
        ];
    }

    public function getAllCampaigns(\DateTime $fromDate = null, \DateTime $toDate = null)
    {
        $groupId = Auth::user()->group_id;
        $bind = ['groupid' => $groupId];

        if (!empty($fromDate) && !empty($toDate)) {
            list($fromDate, $toDate) = $this->dateRange($fromDate, $toDate);

            // convert to datetime strings
            $startDate = $fromDate->format('Y-m-d H:i:s');
            $endDate = $toDate->format('Y-m-d H:i:s');

            $bind = array_merge($bind, ['startdate' => $startDate, 'enddate' => $endDate]);
        }

        $sql = '';
        $union = '';
        foreach (Auth::user()->getDatabaseArray() as $db) {
            $sql .= "$union SELECT CampaignName AS Campaign
        FROM [$db].[dbo].[Campaigns]
        WHERE isActive = 1
        AND GroupId = :groupid
        AND CampaignName != ''";

            if (!empty($fromDate) && !empty($toDate)) {
                $sql .= " AND Date >= :startdate AND Date < :enddate";
            }

            $union = ' UNION';
        }
        $sql .= " ORDER BY Campaign";

        $results = $this->resultsToList($this->runSql($sql, $bind));

        array_unshift($results, '_MANUAL_CALL_');
        return $results;
    }

    public function getAllSubcampaigns(\DateTime $fromDate = null, \DateTime $toDate = null)
    {
        return [];
    }

    public function getAllInboundSources()
    {
        $groupId = Auth::user()->group_id;
        $bind = ['groupid' => $groupId];

        $sql = '';
        $union = '';
        foreach (Auth::user()->getDatabaseArray() as $db) {
            $sql .= "$union SELECT InboundSource, Description
        FROM [$db].[dbo].[InboundSources]
        WHERE GroupId = :groupid";

            $union = ' UNION';
        }
        $sql .= " ORDER BY Description, InboundSource";

        $results = $this->resultsToList($this->runSql($sql, $bind));


        return $results;
    }

    public function getAllReps($rollups = false)
    {
        $groupId = Auth::user()->group_id;
        $bind = ['groupid' => $groupId];

        $sql = '';
        $union = '';
        foreach (Auth::user()->getDatabaseArray() as $db) {
            $sql .= " $union SELECT RepName
        FROM [$db].[dbo].[Reps]
        WHERE isActive = 1
        AND GroupId = :groupid";

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

    public function getAllCallStatuses()
    {
        $groupId = Auth::user()->group_id;
        $bind = ['groupid' => $groupId];

        $sql = '';
        $union = '';
        foreach (Auth::user()->getDatabaseArray() as $db) {
            $sql .= "$union SELECT DISTINCT CallStatus
        FROM [$db].[dbo].[DialingResults]
        WHERE GroupId = :groupid";

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
        $tz = Auth::user()->tz;

        $fromDate = localToUtc($start, $tz);
        $toDate = localToUtc($end, $tz);

        return [$fromDate, $toDate];
    }

    private function runSql($sql, $bind)
    {
        $db = Auth::user()->db;
        config(['database.connections.sqlsrv.database' => $db]);

        // try {
        $results = DB::connection('sqlsrv')->select(DB::raw($sql), $bind);
        // } catch (\Exception $e) {
        //     $results = [];
        // }

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

        $results = $this->executeReport();

        return $results;
    }
}
