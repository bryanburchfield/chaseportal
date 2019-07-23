<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\ReportService;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Log;


class ReportController extends Controller
{

    protected $reportName;
    protected $reportservice;

    public function __construct(Request $request)
    {
        $this->reportName = Str::studly($request->report);
        $this->reportservice = new ReportService($this->reportName);
    }

    public function index(Request $request)
    {
        $results = [];
        // Push old input to form
        $request->flash();

        return $this->returnView($results);
    }

    public function runReport(Request $request)
    {
        $results = $this->reportservice->getResults($request);

        // check for errors
        if (is_object($results)) {
            return $this->returnView([], $results);
        }
        // Push old input to form
        $request->flash();

        return $this->returnView($results);
    }

    public function returnView($results, MessageBag $errors = null)
    {
        $view = $this->reportservice->viewName();
        $pagedata = $this->reportservice->getPageData();
        $filters = $this->reportservice->getFilters();

        return view($view)->with(array_merge($filters, $pagedata, ['results' => $results]))->withErrors($errors);
    }

    //////////////////////
    // Ajax targets follow
    //////////////////////

    public function updateReport(Request $request)
    {
        // form_data comes across as a url string
        parse_str($request->form_data, $output);
        foreach ($output as $k => $v) {
            $request->request->add([$k => $v]);
        }

        // Log::debug($request);

        $errors = [];
        $results = $this->reportservice->getResults($request);

        // check for errors
        if (is_object($results)) {
            $errors = $results;
            $results = [];
        }

        $data = array_merge(['results' => $results], $this->reportservice->getPageData());

        return [
            'table' => view('shared.reporttable')->with($data)->render(),
            'pag' => view('shared.reportpagination')->with($data)->render(),
            'errors' => view('shared.reporterrors')->withErrors($errors)->render(),
            'extras' => $this->reportservice->extras,
        ];
    }

    public function getSubcampaigns()
    {
        $results = $this->reportservice->getAllSubcampaigns();

        return ['results' => $results];
    }

}
