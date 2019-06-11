<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Campaign;

class AdminOutboundDashController extends Controller
{
    use DashTraits;

    public function index(Request $request)
    {
        $this->getSession();

        $groupId = Auth::user()->group_id;
        $campaigns = Campaign::where('GroupId', $groupId)->where('IsActive', 1)->pluck('CampaignName')->toArray();
        natcasesort($campaigns);
        array_unshift($campaigns, 'Total');

        $jsfile[] = "adminoutbounddash.js";
        $cssfile[] = "adminoutbounddash.css";

        $data = [
            'isApi' => $this->isApi,
            'campaign' => $this->campaign,
            'datefilter' => $this->dateFilter,
            'inorout' => $this->inorout,
            'campaign_list' => $campaigns,
            'curdash' => 'adminoutbounddash',
            'jsfile' => $jsfile,
            'cssfile' => $cssfile,
        ];
        return view('adminoutbounddash')->with($data);
    }
}
