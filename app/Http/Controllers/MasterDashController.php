<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Campaign;
use Illuminate\Http\RedirectResponse;


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

    public function selectedReport(Request $request){
        return view('reports.call_details');
        // return redirect()->route('master/reports/call_details');
        $return['report'] = $request->report;
        echo json_encode($return);
    }

    public function recipients()
    {
        return 'Recipients';
    }

    public function admin()
    {
        return view('master.admin');
    }

    public function updateReport(Request $request)
    {
        //
    }

    public function addUser(Request $request)
    {
        //
    }

    public function deleteUser(Request $request)
    {
        //
    }

    public function test(){

    }
}
