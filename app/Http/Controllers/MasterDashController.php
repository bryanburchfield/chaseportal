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

    use DashTraits;

    public function index(Request $request)
    {
        $this->getSession($request);

        $campaigns = $this->campaignGroups();
        $db_list = $this->getDatabaseArray();

        $this->currentDash = session('currentDash', 'inbounddash');
        session(['currentDash' => $this->currentDash]);

        $jsfile[] = $this->currentDash . ".js";

        $cssfile[] = $this->currentDash . ".css";
        $cssfile[] = "master.css";

        $page['menuitem'] = $this->currentDash;

        $page['type'] = 'dash';
        if ($this->currentDash == 'kpidash') {
            $page['type'] = 'kpi_page';
        }

        $dashbody = 'dashboards.' . $this->currentDash;

        $data = [
            'campaign' => $this->campaign,
            'dateFilter' => $this->dateFilter,
            'inorout' => $this->inorout,
            'campaign_list' => $campaigns,
            'currentDash' => $this->currentDash,
            'jsfile' => $jsfile,
            'cssfile' => $cssfile,
            'page' => $page,
            'dashbody' => $dashbody,
            'has_multiple_dbs' => Auth::user()->isMultiDb(),
            'db_list' => $db_list
        ];

        return view('masterdash')->with($data);
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

    public function updateLangDisplay(Request $request)
    {
        $user = Auth::user();
        $display_lang = (int) $request->lang_displayed;
        User::where('id', $user->id)->update(array('language_displayed' => $display_lang));

        return redirect()->back();
    }

    public function updateTheme(Request $request)
    {
        $user = Auth::user();
        $theme = (int) $request->theme;
        $theme = ($theme ? 'dark' : 'light');
        User::where('id', $user->id)->update(array('theme' => $theme));

        if ($user->user_type == 'admin') {
            return redirect('/dashboards/admin#settings');
        } else {
            return redirect()->back();
        }
    }
}
