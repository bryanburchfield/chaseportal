<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Campaign;
use App\AutomatedReport;

class MasterDashController extends Controller
{
    public $currentDash;

    use DashTraits;

    public function index(Request $request)
    {
        $this->getSession($request);

        $groupId = Auth::user()->group_id;
        $campaigns = Campaign::where('GroupId', $groupId)->where('IsActive', 1)->pluck('CampaignName')->toArray();
        natcasesort($campaigns);
        array_unshift($campaigns, 'Total');

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
        session(['currentDash' => $request->dashboard]);

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

    public function automatedReportList()
    {
        $list = [
            'agent_analysis' => 'Agent Analysis',
            'agent_pause_time' => 'Agent Pause Time',
            'agent_summary_campaign' => 'Agent Summary by Campaign',
            'agent_summary' => 'Agent Summary',
            'agent_summary_subcampaign' => 'Agent Summary by Subcampaign',
            'agent_timesheet' => 'Agent Timesheet',
            'campaign_call_log' => 'Campaign Call Log',
            'campaign_summary' => 'Campaign Summary',
            'inbound_summary' => 'Inbound Summary',
            'production_report' => 'Production Report',
            'production_report_subcampaign' => 'Production by Subcampaign Report',
            'shift_report' => 'Shift Report',
            'subcampaign_summary' => 'Subcampaign Summary',
        ];

        return $list;
    }

    public function getAutomatedReports()
    {


        $user_id= Auth::user()->id;
        $selected = AutomatedReport::pluck('report')->where('user_id', $user_id)->get()->toArray();
        $selected = array_column($selected, 'report');

        $list = [];
        foreach($this->automatedReportList() as $k => $v) {
            $list[] = [
                'report' => $k,
                'name' => $v,
                'selected' => (in_array($k, $selected)) ? 1 : 0
            ];
        }

        return $list;
    }

    public function reportSettings()
    {

        $page['menuitem'] = 'reports';
        $page['type'] = 'other';

        $data = [
            'page'=>$page,
            'reports'=>$this->getAutomatedReports()
        ];

        return view('reportsettings')->with($data);
    }
}
