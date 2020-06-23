<?php

namespace App\Http\Controllers;

use App\Models\AutomatedReport;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\ReportService;
use Illuminate\Support\Facades\App;
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
        // Check if group_id = -1 then force user to select (for sso reports)
        if (Auth::user()->group_id == -1) {
            return $this->setGroupForm();
        }

        $this->reportservice->report->setDates();

        // Push old input to form
        $request->flash();

        return $this->returnView();
    }

    private function setGroupForm()
    {
        $data = [
            'report' => $this->reportName,
            'groups' => Group::allGroups(),
        ];

        return view('reports.choose_group')->with($data);
    }

    public function setGroup(Request $request)
    {
        Auth::user()->group_id = $request->group_id;
        Auth::user()->save();

        return $this->index($request);
    }

    public function info()
    {
        $pagedata = $this->reportservice->getPageData();
        $report_info = $this->reportservice->getReportInfo();

        return view('reports.info')
            ->with(array_merge(
                $pagedata,
                $report_info
            ));
    }

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
    }

    public function returnView($results = [], MessageBag $errors = null)
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
            $errors = $results->all();
            $results = [];
        }

        $data = array_merge(['results' => $results], $this->reportservice->getPageData());

        return [
            'table' => view('shared.reporttable')->with($data)->render(),
            'pag' => view('shared.reportpagination')->with($data)->render(),
            'errors' => $errors,
            'extras' => $this->reportservice->report->extras,
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
        if (!$user) {
            return;
        }

        if (Auth::check()) {
            Auth::logout();
        }

        // set a flag so the audit trail doesn't pick it up
        session(['isCron' => 1]);
        Auth::login($user);

        if (in_array($user->language, config('localization.locales'))) {
            App::setLocale($user->language);
        }

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
