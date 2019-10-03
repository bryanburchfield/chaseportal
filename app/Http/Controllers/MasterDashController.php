<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Traits\DashTraits;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class MasterDashController extends Controller
{
    public $currentDash;

    use DashTraits;

    public function index(Request $request)
    {
        $this->getSession($request);

        $campaigns = $this->campaignGroups();
        $db_list = $this->getDatabaseArray();

        $this->currentDash = session('currentDash', 'admindash');
        session(['currentDash' => $this->currentDash]);

        $jsfile[] = $this->currentDash . ".js";
        // $jsfile[] = "master.js";
        // $jsfile[] = "masternav.js";

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
            'datefilter' => $this->dateFilter,
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
            'user' => Auth::user(),
            'page' => $page,
            'success' => $success,
        ];

        return view('dashboards.mysettings')
            ->with($data);
    }

    public function updateUserSettings(Request $request)
    {
        $user = Auth::user();
        $errors = [];
        $success = [];
        $errors = [];

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
            'current_password' => 'required',
            'new_password' => 'nullable|min:8|different:current_password',
            'conf_password' => 'same:new_password',
        ]);

        /// check if current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            $errors[] = 'Current password is incorrect';
        }

        if (!empty($errors)) {
            return redirect()->back()->withInput()->withErrors($errors);
        }

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
}
