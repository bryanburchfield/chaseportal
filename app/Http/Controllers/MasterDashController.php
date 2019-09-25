<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Traits\DashTraits;
use Illuminate\Support\Facades\Auth;
use App\User;
use Illuminate\Support\Facades\Hash;

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

    public function showSettings()
    {
        $user = Auth::user();

        $page['menuitem'] = 'other';
        $page['type'] = 'other';
        $data = [
            'user' => $user,
            'page' => $page,
        ];

        return view('dashboards.mysettings')->with($data);
    }

    public function updateUserSettings(Request $request)
    {   
        $user = Auth::user();
        $return=[
            'errors' => [],
            'success' => []
        ];

        /// check if name or email is used by another user
        $user_check = User::where('id', '!=', $request->id)
            ->where(function ($query) use ($request) {
                $query->where('name', $request->name)
                    ->orWhere('email', $request->email);
            })
            ->first();

        /// check if current password is correct
        if(Hash::make($request->current_password) != $user->password){
            array_push($return['errors'], 'Current password is incorrect');
        }

        /// check if new password matches confirm password
        if($request->new_password != $request->conf_password){
            array_push($return['errors'], 'New password does not match');
        }

        if ($user_check) {
            array_push($return['errors'], 'Name or email in use by another user');
        } else {
            $user = User::findOrFail($request->id);
            $user->update($request->all());
            array_push($return['success'], $user);
        }

        return $return;
        // return view('dashboards.mysettings')->with($return);
    }
}
