<?php

namespace App\Services;

use App\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportService
{
    private $report;
    private $reportName;

    public function __construct($reportName)
    {
        $class = "App\Services\Reports\\$reportName";

        if (!class_exists($class)) {
            abort(404);
        }

        $this->reportName = $reportName;
        $this->report = new $class();
    }

    public function viewName()
    {
        $view = 'reports.' . Str::snake($this->reportName);

        if (!view()->exists($view)) {
            abort(404);
        }

        return $view;
    }

    public function getFilters()
    {
        return $this->report->getFilters();
    }

    public function getPageData()
    {
        return $this->report->getPageData();
    }

    public function getResults(Request $request)
    {
        return $this->report->getResults($request);
    }

    ////////////////////////
    // Static methods follow
    ////////////////////////



    public static function getAllCampaigns(\DateTime $fromDate = null, \DateTime $toDate = null)
    {
        $groupId = Auth::user()->group_id;
        $bind = ['groupid' => $groupId];

        if (!empty($fromDate) && !empty($toDate)) {
            list($fromDate, $toDate) = self::dateRange($fromDate, $toDate);

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

        $results = self::runSql($sql, $bind);

        return $results;
    }

    public static function getAllInboundSources()
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

        $results = self::runSql($sql, $bind);

        return $results;
    }

    public static function getAllReps($rollups = false)
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

        $results = self::runSql($sql, $bind);

        if ($rollups) {
            array_unshift($results, '[All Unanswered]');
            array_unshift($results, '[All Answered]');
        }

        return $results;
    }

    public static function getAllCallStatuses()
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

        $results = self::runSql($sql, $bind);

        return $results;
    }

    public static function getAllCallTypes()
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

    private static function dateRange($start, $end)
    {
        $tz = Auth::user()->tz;

        $fromDate = localToUtc($start, $tz);
        $toDate = localToUtc($end, $tz);

        return [$fromDate, $toDate];
    }

    private static function runSql($sql, $bind)
    {
        $db = Auth::user()->db;
        config(['database.connections.sqlsrv.database' => $db]);

        $results = DB::connection('sqlsrv')->select(DB::raw($sql), $bind);

        if (count($results)) {
            // convert array of objects to array of arrays
            $results = json_decode(json_encode($results), true);

            // flatten array.  If 2 cols then create k=>v pairs

            if (count($results[0]) == 1) {
                $key = implode('', array_keys($results[0]));
                $results = array_column($results, $key);
            } elseif (count($results[0]) == 2) {
                $arr = [];
                foreach ($results as $rec) {
                    $vals = array_values($rec);
                    $arr[$vals[0]] = $vals[1];
                }
                $results = $arr;
            }
        }
        return $results;
    }
}
