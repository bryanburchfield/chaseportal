<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Str;


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
        $pagedata['report'] = $this->reportName;
        $pagedata['page']['menuitem'] = 'reports';
        $pagedata['page']['type'] = 'report';
        $pagedata['jsfile'] = ['site.js'];

        return array_merge($pagedata, ['params' => $this->report->params]);
    }

    public function getResults(Request $request)
    {
        return $this->report->getResults($request);
    }
}
