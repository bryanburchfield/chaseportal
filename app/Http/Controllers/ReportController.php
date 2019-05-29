<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\ReportService;


class ReportController extends Controller
{

    protected $reportName;
    protected $reportservice;

    public function __construct(Request $request)
    {
        $this->reportName = Str::studly($request->report);
        $this->reportservice = new ReportService($this->reportName);
    }

    public function index()
    {
        $results = [];

        return $this->returnView($results);
    }

    public function runReport(Request $request)
    {
        $results = $this->reportservice->getResults($request);

        return $this->returnView($results);
    }

    public function returnView($results)
    {
        $view = $this->reportservice->viewName();
        $pagedata = $this->reportservice->getPageData();
        $filters = $this->reportservice->getFilters();

        $pagedata['report'] = $this->reportName;
        $pagedata['page']['menuitem'] = 'reports';
        $pagedata['page']['type'] = 'report';

        return view($view)->with(array_merge($filters, $pagedata, $results));
    }
}
