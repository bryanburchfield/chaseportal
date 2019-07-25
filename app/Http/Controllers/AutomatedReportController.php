<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\AutomatedReport;

class AutomatedReportController extends Controller
{
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

    private function getAutomatedReports()
    {
        $user_id = Auth::user()->id;
        $selected = AutomatedReport::where('user_id', $user_id)->pluck('report')->toArray();

        $list = [];
        foreach ($this->automatedReportList() as $k => $v) {
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
            'page' => $page,
            'reports' => $this->getAutomatedReports(),
        ];

        return view('reportsettings')->with($data);
    }

    public function toggleAutomatedReport(Request $request)
    {

        $active = (int) $request->active;
        $user_id = Auth::user()->id;
        $report = $request->report;

        if ($active) {
            AutomatedReport::create(['user_id' => $user_id, 'report' => $report]);
        } else {
            $uncheck_report = AutomatedReport::where('user_id', '=', $user_id)->where('report', '=', $report);
            $uncheck_report->delete();
        }
    }
}
