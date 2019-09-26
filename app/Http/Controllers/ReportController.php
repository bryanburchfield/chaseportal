<?php

namespace App\Http\Controllers;

use App\AutomatedReport;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\ReportService;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Auth;

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

    // public function runReport(Request $request)
    // {
    //     $results = $this->reportservice->getResults($request);

    //     // check for errors
    //     if (is_object($results)) {
    //         return $this->returnView([], $results);
    //     }
    //     // Push old input to form
    //     $request->flash();

    //     return $this->returnView($results);
    // }

    public function exportReport(Request $request)
    {
        $request = $this->parseRequest($request);

        $request->request->add(['all' => 1]);
        $request->request->add(['export' => 1]);

        $function = strtolower($request->format) . 'Export';

        if (method_exists($this->reportservice->report, $function)) {
            return $this->reportservice->report->$function($request);
        }

        abort(404);
    }

    public function emailReport(Request $request)
    {
        $request = $this->parseRequest($request);

        $request->request->add(['all' => 1]);

        return $this->reportservice->report->emailReport($request);

        abort(404);
    }

    public function returnView($results, MessageBag $errors = null)
    {
        $view = $this->reportservice->viewName();
        $pagedata = $this->reportservice->getPageData();
        $filters = $this->reportservice->report->getFilters();
        $params = $this->reportservice->report->params;

        return view($view)
            ->with(array_merge(
                ['filters' => $filters],
                $pagedata,
                $params,
                ['results' => $results]
            ))
            ->withErrors($errors);
    }

    private function parseRequest(Request $request)
    {
        // form_data comes across as a url string
        parse_str($request->form_data, $output);
        foreach ($output as $k => $v) {
            $request->request->add([$k => $v]);
        }

        return $request;
    }

    //////////////////////
    // Ajax targets follow
    //////////////////////

    public function updateReport(Request $request)
    {
        $request = $this->parseRequest($request);

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
            'errors' => $errors->all(),
            'extras' => $this->reportservice->extras,
            'params' => $this->reportservice->report->params,
        ];
    }

    public function getCampaigns(Request $request)
    {
        $fromDate = $request->fromdate;
        $toDate = $request->todate;

        $results = $this->reportservice->report->getAllCampaigns($fromDate, $toDate);

        return ['campaigns' => array_values($results)];
    }

    public function getSubcampaigns(Request $request)
    {
        $campaign = $request->campaign;

        $results = $this->reportservice->report->getAllSubcampaigns($campaign);

        return ['subcampaigns' => array_values($results)];
    }

    /**
     * Return all automated report records
     * We may add some conditionals later
     *
     * @return AutomatedReport collection
     */
    public static function cronDue()
    {
        return AutomatedReport::orderBy('user_id')->get();
    }

    /**
     * Run report in background (from scheduler)
     *
     * @param AutomatedReport $automatedReport
     * @return void
     */
    public static function cronRun(AutomatedReport $automatedReport)
    {
        // authenticate as user
        $user = User::where('id', '=', $automatedReport->user_id)->first();
        Auth::logout();
        Auth::login($user);

        // create report controller object
        $request = new Request();
        $request->setMethod('POST');
        $request->request->add(['report' => $automatedReport->report]);
        $report = new ReportController($request);

        // Run report
        $request = new Request();
        $request->setMethod('POST');
        $request->request->add(['form_data' => $automatedReport->filters]);
        $request->request->add(['email' => $user->email]);
        $report->emailReport($request);
    }
}
