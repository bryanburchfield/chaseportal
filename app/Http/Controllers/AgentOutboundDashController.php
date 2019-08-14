<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Campaign;
use \App\Traits\DashTraits;

class AgentOutboundDashController extends Controller
{
    use DashTraits;

    /**
     * Display dashboard
     *
     * @param Request $request
     * @return view
     */
    public function index(Request $request)
    {
        $this->getSession($request);

        $groupId = Auth::user()->group_id;
        $campaigns = Campaign::where('GroupId', $groupId)->where('IsActive', 1)->pluck('CampaignName')->toArray();
        natcasesort($campaigns);
        array_unshift($campaigns, 'Total');

        $jsfile[] = "agentoutbounddash.js";
        $cssfile[] = "agentoutbounddash.css";

        $data = [
            'isApi' => $this->isApi,
            'campaign' => $this->campaign,
            'datefilter' => $this->dateFilter,
            'inorout' => $this->inorout,
            'campaign_list' => $campaigns,
            'curdash' => 'agentoutbounddash',
            'jsfile' => $jsfile,
            'cssfile' => $cssfile,
        ];
        return view('agentoutbounddash')->with($data);
    }
}
