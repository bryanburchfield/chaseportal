<?php

namespace App\Http\Controllers;

use App\Models\AutomatedReport;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\ReportService;
use App\Traits\SqlServerTraits;
use App\Traits\TimeTraits;
use Illuminate\Support\Facades\App;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    use SqlServerTraits;
    use TimeTraits;

    protected $reportName;
    protected $reportservice;

    public function __construct(Request $request)
    {
        $this->reportName = Str::studly($request->report);
        $this->reportservice = new ReportService($this->reportName);
    }

    public function index(Request $request)
    {
        // SSO Checks
        if (session('isSso', 0)) {
            // Check if group_id = -1 then force user to select
            if (Auth::user()->group_id !== -1) {
                // see if they have relative camps/reps
                $this->getSsoRestrictions();
            }

            // Set timezone if not already
            if (empty(Auth::user()->tz)) {
                Auth::user()->tz = $this->getSsoTz();
                Auth::user()->save();
            }
        }

        $this->reportservice->report->setDates();

        // Push old input to form
        $request->flash();

        return $this->returnView();
    }

    public function setGroup(Request $request)
    {
        Auth::user()->group_id = $request->group_id;
        Auth::user()->save();

        if ($request->ajax()) {
            return ['status' => 'success'];
        }

        return redirect()->action('ReportController@index', ['report' => $request->report]);
    }

    public function setTimezone(Request $request)
    {
        Auth::user()->tz = $request->tz;
        Auth::user()->save();

        if ($request->ajax()) {
            return ['status' => 'success'];
        }

        return redirect()->action('ReportController@index', ['report' => $request->report]);
    }

    private function getSsoTz()
    {
        $sql = "SET NOCOUNT ON;

    DECLARE 
    @TimeZoneStr varchar(3),
    @TimeZone int

	SET @TimeZone = dbo.GetSettingEx (:group, '', 'TimeZone', 2)

	SET @TimeZoneStr='EST'	

	SELECT @TimeZoneStr = timezone
	FROM StateTimeZones
	WHERE id = @TimeZone

    SELECT @TimeZoneStr as TZ";

        $bind = ['group' => Auth::user()->group_id];

        $results = $this->runSql($sql, $bind);

        if (empty($results)) {
            $tz = 'EST';
        } else {
            $tz = $results[0]['TZ'];
        }

        return $this->abbrToText($tz);
    }

    private function getSsoRestrictions()
    {
        $sql = "SET NOCOUNT ON;
      SELECT 'Camps' = dbo.UseRelativeCampaigns(:username1, 1);
      SELECT 'Reps' = dbo.UseRelativeReps(:username2);";

        $bind = [
            'username1' => session('ssoUsername'),
            'username2' => session('ssoUsername'),
        ];

        list($camps, $reps) = $this->runMultiSql($sql, $bind);

        session(['ssoRelativeCampaigns' => $camps[0]['Camps']]);
        session(['ssoRelativeReps' => $reps[0]['Reps']]);
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
                ['groups' => Group::allGroups()],
                ['timezone_array' => $this->timezones()],
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
            'columns' => array_values($this->reportservice->report->params['columns']),
            'results' => $results,
            // 'table' => view('shared.reporttable')->with($data)->render(),
            // 'pag' => view('shared.reportpagination')->with($data)->render(),
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
