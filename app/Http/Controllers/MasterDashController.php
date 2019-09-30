<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Traits\DashTraits;
use Illuminate\Support\Facades\Auth;
use App\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\MessageBag;

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

    public function showSettings($success = [], $errors = [])
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

        return view('dashboards.mysettings')->with($data)->withErrors($errors);
    }

    public function updateUserSettings(Request $request)
    {
        $user = Auth::user();
        $errors = [];
        $success = [];

        /// check if name or email is used by another user
        $user_check = User::where('id', '!=', $user->id)
            ->where(function ($query) use ($request) {
                $query->where('name', $request->name)
                    ->orWhere('email', $request->email);
            })
            ->first();

        if ($user_check) {
            $errors[] = 'Name or email in use by another user';
        }

        /// check if current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            $errors[] = 'Current password is incorrect';
        }

        /// check if new password matches confirm password
        if ($request->new_password != $request->conf_password) {
            $errors[] = 'New password does not match';
        } else {
            if ($request->current_password == $request->new_password) {
                $errors[] = 'New password must be different from current password';
            }
        }

        if (empty($errors)) {
            $user = Auth::user();

            $update = [
                'name' => $request->name,
                'email' => $request->email,
            ];

            if (!empty($request->new_password)) {
                $update['password'] = Hash::make($request->new_password);
            }

            $success[] = $user->update($update);
        }

        return $this->showSettings($success, $errors);
    }
}
