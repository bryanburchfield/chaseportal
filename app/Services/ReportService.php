<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Http\Request;

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

    public static function getAllCampaigns($fromDate = null, $toDate = null)
    {
        return [];
    }

    public static function getAllInboundSources()
    {
        return [];
    }

    public static function getAllReps()
    {
        return [];
    }

    public static function getAllCallStatuses()
    {
        return [];
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
}
