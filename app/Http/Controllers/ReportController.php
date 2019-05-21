<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Campaign;
use App\DialingResult;
use App\AgentActivity;
use App\InboundSource;
use App\Rep;
use App\Dispo;

class ReportController extends Controller
{
	use DashTraits;

    public function index(Request $request)
    {
        $view = 'reports.' . $request->report;
        if (!view()->exists($view)) {
            abort(404, $view . " not found");
        }

        $jsfile = [];
        $cssfile = [];

        $this->getSession();

        $groupId = Auth::user()->group_id;
        $campaigns = Campaign::where('GroupId', $groupId)->where('IsActive', 1)->pluck('CampaignName')->toArray();
        natcasesort($campaigns);
        array_unshift($campaigns, 'Total');

        $call_status = Dispo::where('GroupId', $groupId)->orWhere('IsSystem', 1)->pluck('Disposition')->sortBy('Disposition')->toArray();
		$inbound_sources = InboundSource::where('GroupId', $groupId)->pluck('InboundSource', 'Description')->sortBy('Description')->toArray();     
        $rep = Rep::where('GroupId', $groupId)->where('IsActive', 1)->pluck('RepName')->toArray();

        $call_types = [
            0 => 'Outbound',
            1 => 'Inbound',
            2 => 'Manual',
            3 => 'Transferred',
            4 => 'Conference',
            5 => 'Progresive',
            6 => 'TextMessage',
        ];

        $report = $request->report;
        $page['menuitem'] = 'reports';
        $page['type'] = 'report';

        $data = [
            'jsfile' => $jsfile,
            'cssfile' => $cssfile,
            'campaigns' => $campaigns,
            'inbound_sources' => $inbound_sources,
            'rep' => $rep,
            'call_status' => $call_status,
            'call_types' => $call_types,
            'report' => $report,
            'page' => $page
        ];

        return view($view)->with($data);
    }

  
}