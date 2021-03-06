<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Traits\DashTraits;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\User;
use Illuminate\Support\Carbon;

class MasterDashController extends Controller
{
    public $currentDash;
    public $data;
    public $cssfile = [];
    public $includescriptfile = [];

    use DashTraits;

    public function index(Request $request)
    {
        $this->getSession($request);

        $campaigns = $this->campaignGroups();
        $db_list = $this->getDatabaseArray();

        $this->currentDash = session('currentDash', 'inbounddash');
        session(['currentDash' => $this->currentDash]);

        $jsfile[] = $this->currentDash . ".js";

        $page['menuitem'] = $this->currentDash;

        $page['type'] = 'dash';

        if ($this->currentDash == 'kpidash') {
            $page['type'] = 'kpi_page';
        }

        $page['sidenav'] = $this->sideNav($this->currentDash);

        $dashbody = 'dashboards.' . $this->currentDash;

        $data = [
            'campaign' => $this->campaign,
            'dateFilter' => $this->dateFilter,
            'inorout' => $this->inorout,
            'campaign_list' => $campaigns,
            'currentDash' => $this->currentDash,
            'jsfile' => $jsfile,
            'cssfile' => $this->cssfile,
            'includescriptfile' => $this->includescriptfile,
            'page' => $page,
            'data' => $this->data,
            'dashbody' => $dashbody,
            'has_multiple_dbs' => Auth::user()->isMultiDb(),
            'db_list' => $db_list,
        ];

        return view('masterdash')->with($data);
    }

    private function sideNav($page)
    {
        $sidenav = [
            'admindistinctagentdash' => 'admin',
            'admindurationdash' => 'admin',
            'compliancedash' => 'admin',
            'kpidash' => 'main',
            'leaderdash' => 'main',
        ];

        return (isset($sidenav[$page])) ? $sidenav[$page] : 'dashboards';
    }

    public function demoLogin(Request $request)
    {
        $token = $request->token;

        // find first user record with that token
        $user = User::where('app_token', $token)->first();

        if ($user === null) {
            abort(403, 'Invalid token');
        }

        // Check that they're a demo user
        if (!$user->isDemo()) {
            return redirect('/');
        }

        $expiration = Carbon::parse($user->expiration);

        // See if they're expired
        if ($expiration < Carbon::now() || $user->isType('expired')) {
            return view('demo.expired', ['user' => $user]);
        }

        // Login that user
        Auth::loginUsingId($user->id);

        return view('demo.welcome', ['user' => $user]);
    }

    public function adminDistinctAgentDashboard(Request $request)
    {
        $request->merge(['dashboard' => 'admindistinctagentdash']);
        $this->setDashboard($request);

        $this->cssfile[] = 'https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css';

        return $this->index($request);
    }

    public function adminDurationDashboard(Request $request)
    {
        $request->merge(['dashboard' => 'admindurationdash']);
        $this->setDashboard($request);

        return $this->index($request);
    }

    public function inboundDashboard(Request $request)
    {
        $request->merge(['dashboard' => 'inbounddash']);
        $this->setDashboard($request);

        return $this->index($request);
    }

    public function outboundDashboard(Request $request)
    {
        $request->merge(['dashboard' => 'outbounddash']);
        $this->setDashboard($request);

        return $this->index($request);
    }

    public function trendDashboard(Request $request)
    {
        $request->merge(['dashboard' => 'trenddash']);
        $this->setDashboard($request);

        return $this->index($request);
    }

    public function leaderDashboard(Request $request)
    {
        $request->merge(['dashboard' => 'leaderdash']);
        $this->setDashboard($request);

        return $this->index($request);
    }

    public function complianceDashboard(Request $request)
    {
        $request->merge(['dashboard' => 'compliancedash']);
        $this->setDashboard($request);

        $this->cssfile[] = 'https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css';

        return $this->index($request);
    }

    public function realtimeAgentDashboard(Request $request)
    {
        $request->merge(['dashboard' => 'realtimeagentdash']);
        $this->setDashboard($request);

        $controller = new RealTimeDashboardController();

        $this->data = $controller->index();

        $this->includescriptfile[] = 'shared.realtimeagentdashscript';

        return $this->index($request);
    }

    public function kpi(Request $request)
    {
        $request->merge(['dashboard' => 'kpidash']);
        $this->setDashboard($request);

        return $this->index($request);
    }

    public function setDashboard(Request $request)
    {
        session(['currentDash' => $request->dashboard]);

        // ajax return
        return ['set_dash' => $request->dashboard];
    }

    public function showReport(Request $request)
    {
        return redirect()->action('ReportController@index', ['report' => $request->report_option]);
    }

    public function showSettings($success = null)
    {
        $page = [
            'menuitem' => 'settings',
            'type' => 'other',
        ];

        $data = [
            'page' => $page,
            'success' => $success,
        ];

        return view('dashboards.mysettings')
            ->with($data);
    }

    public function updateUserSettings(Request $request)
    {
        $user = Auth::user();
        $success = [];

        $request->validate([
            'name' => [
                'required',
                Rule::unique('users')->ignore($user->id),
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'current_password' => [
                'required',
                function ($attribute, $value, $fail) use ($user) {
                    if (!Hash::check($value, $user->password)) {
                        $fail('The current password is incorrect');
                    }
                },

            ],
            'new_password' => 'nullable|min:8|confirmed|different:current_password',
        ]);

        $update = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if (!empty($request->new_password)) {
            $update['password'] = Hash::make($request->new_password);
        }

        $success[] = $user->update($update);

        return $this->showSettings($success);
    }

    public function landingPage(Request $request)
    {
        if (Auth::guest()) {
            return view('auth.login');
        }

        $this->getSession($request);

        $page = [
            'menuitem' => '',
            'type' => 'main',
            'sidenav' => 'main',
        ];

        $data = [
            'cssfile' => $this->cssfile,
            'page' => $page,
        ];

        return view('index')->with($data);
    }
}
