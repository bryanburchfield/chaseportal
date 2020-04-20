<?php

namespace App\Http\Controllers;

use App\Models\ContactsPlaybook;
use App\Traits\CampaignTraits;
use App\Traits\SqlServerTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlaybookController extends Controller
{
    use CampaignTraits;
    use SqlServerTraits;

    /**
     * Playbook campaigns index
     * 
     * @return View|Factory 
     */
    public function index()
    {
        $page = [
            'menuitem' => 'tools',
            'type' => 'other',
        ];

        $data = [
            'page' => $page,
            'jsfile' => ['playbook_campaigns.js'],
            'group_id' => Auth::user()->group_id,
            'campaigns' => $this->getAllCampaigns(),
            'contacts_playbooks' => $this->getPlaybooks(),
        ];

        return view('tools.playbook.playbooks')->with($data);
    }

    /**
     * Campaigns configured for this group
     * 
     * @return mixed 
     */
    private function getPlaybooks()
    {
        return ContactsPlaybook::where('group_id', Auth::User()->group_id)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get Subcampaigns (ajax)
     * 
     * @param Request $request 
     * @return array[] 
     */
    public function getSubcampaigns(Request $request)
    {
        $results = $this->getAllSubcampaignsWithNone($request->campaign);

        return ['subcampaigns' => $results];
    }

    /**
     * Append "!!none!!" to the list of subcampaigns
     * 
     * @param mixed $campaign 
     * @return mixed 
     */
    private function getAllSubcampaignsWithNone($campaign)
    {
        $results = $this->getAllSubcampaigns($campaign);
        $results = ['!!none!!' => trans('tools.no_subcampaign')] + $results;

        return $results;
    }
}
