<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Campaign;
use Illuminate\Http\RedirectResponse;
use App\System;
use App\User;

class MasterDashController extends Controller
{
    public $currentDash;

    use DashTraits;

    public function index(Request $request)
    {
        $this->getSession();

        $groupId = Auth::user()->group_id;
        $campaigns = Campaign::where('GroupId', $groupId)->where('IsActive', 1)->pluck('CampaignName')->toArray();
        natcasesort($campaigns);
        array_unshift($campaigns, 'Total');

        $this->currentDash = Session::get('currentDash', 'admindash');
        Session::put([
            'currentDash' => $this->currentDash,
        ]);

        Log::debug($this->currentDash);

        $jsfile[] = $this->currentDash . ".js";
        $jsfile[] = "master.js";
        $jsfile[] = "masternav.js";

        $cssfile[] = $this->currentDash . ".css";
        $cssfile[] = "master.css";

        $page['menuitem'] = $this->currentDash;

        $page['type'] = 'dash';
        if ($this->currentDash == 'kpidash') {
            $page['type'] = 'kpi_page';
        }

        $dashbody = 'master.' . $this->currentDash;

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
        ];
        return view('masterdash')->with($data);
    }

    public function setDashboard(Request $request)
    {
        Session::put([
            'currentDash' => $request->dashboard,
        ]);

        // ajax return
        $return['set_dash'] = $request->dashboard;
        echo json_encode($return);
    }

    public function showReport(Request $request)
    {
        return redirect()->action('ReportController@index', ['report' => $request->report_option]);
    }

    public function recipients()
    {
        return 'Recipients';
    }

    public function admin()
    {
        $this->getSession();
        $groupId = Auth::user()->group_id;

        $timezones = System::all()->sortBy('current_utc_offset')->toArray();
        $timezone_array = ['' => 'Select One'];
        foreach ($timezones as $tz) {
            $timezone_array[$tz['name']] = '[' . $tz['current_utc_offset'] . '] ' . $tz['name'];
        }

        $dbs = ['' => 'Select One'];
        for ($i = 0; $i < 24; $i++) {
            $dbs['PowerV2_Reporting_Dialer-' . $i] = 'PowerV2_Reporting_Dialer-' . $i;
        }

        $users = User::all()->sortBy('id');

        $page['menuitem'] = 'admin';
        $page['type'] = 'page';
        $data = [
            'page' => $page,
            'timezone_array' => $timezone_array,
            'group_id' => $groupId,
            'dbs' => $dbs,
            'users' => $users,
            'jsfile' => [],
        ];

        return view('master.admin')->with($data);
    }

    public function addUser(Request $request)
    {
        $input = $request->all();
        $input['password'] = Hash::make('password');
        User::create($input);
        return redirect('master/admin');
    }

    public function deleteUser(Request $request)
    {
        $user = User::findOrFail($request->userid)->delete();
        return redirect('master/admin');
    }

}
