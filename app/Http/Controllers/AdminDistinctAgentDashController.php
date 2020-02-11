<?php

namespace App\Http\Controllers;

use App\Traits\DashTraits;
use Illuminate\Http\Request;

class AdminDistinctAgentDashController extends Controller
{
    use DashTraits;

    public function index(Request $request)
    {
        $this->getSession($request);

        $campaigns = $this->campaignGroups();

        $jsfile[] = "admindistinctagentdash.js";
        $cssfile[] = "admindistinctagentdash.css";

        $data = [
            'isApi' => $this->isApi,
            'campaign' => $this->campaign,
            'dateFilter' => $this->dateFilter,
            'inorout' => $this->inorout,
            'campaign_list' => $campaigns,
            'curdash' => 'admindistinctagentdash',
            'jsfile' => $jsfile,
            'cssfile' => $cssfile,
        ];

        return view('admindistinctagentdash')->with($data);
    }
}
