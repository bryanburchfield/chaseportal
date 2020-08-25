<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidPlaybook;
use App\Models\Campaign;
use App\Models\ContactsPlaybook;
use App\Models\PlaybookOptout;
use App\Traits\CampaignTraits;
use App\Traits\SqlServerTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

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
            'menuitem' => 'playbook',
            'sidenav' => 'main',
            'type' => 'other',
        ];

        $data = [
            'page' => $page,
            'jsfile' => ['contacts_playbooks.js'],
            'cssfile' => ['https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css', 'https://cdn.datatables.net/fixedheader/3.1.7/css/fixedHeader.dataTables.min.css'],
            'group_id' => Auth::user()->group_id,
            'campaigns' => $this->getAllCampaigns(),
            'contacts_playbooks' => $this->getPlaybooks(),
        ];

        return view('playbook.playbooks')->with($data);
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
     * Get extra campaigns and subcampaigns (ajax)
     * @param Request $request 
     * @return array 
     */
    public function getExtraCampaigns(Request $request)
    {
        // Find the campaign
        $campaign = Campaign::where('CampaignName', $request->campaign)
            ->where('GroupId', Auth::user()->group_id)
            ->firstOrFail();

        $subcampaigns = $this->getAllSubcampaigns($request->campaign);

        $extra_campaigns = $this->relatedCampaigns($request->campaign);

        return [
            'extra_campaigns' => $extra_campaigns,
            'subcampaigns' => $subcampaigns,
        ];
    }

    /**
     * Return related campaigns that use same advanaced table
     * 
     * @param mixed $campaign 
     * @return array 
     */
    public function relatedCampaigns($campaign)
    {
        // Find the campaign
        $campaign = Campaign::where('CampaignName', $campaign)
            ->where('GroupId', Auth::user()->group_id)
            ->firstOrFail();

        $related_campaigns = [];

        // check that campaign has related campaigns
        if (!empty($campaign->advancedTable->campaigns)) {
            // Get related campaigns by AdvancedTable
            $related_campaigns = $campaign->advancedTable->campaigns;

            // Filter out passed campaign and return only names
            $related_campaigns = $related_campaigns
                ->reject(function ($rec) use ($campaign) {
                    return $rec['CampaignName'] == $campaign->CampaignName;
                })
                ->map(function ($rec) {
                    return $rec['CampaignName'];
                })
                ->toArray();

            $related_campaigns = array_values($related_campaigns);
        }

        return $related_campaigns;
    }

    /**
     * Add a playbook
     * 
     * @param ValidPlaybook $request 
     * @return string[] 
     */
    public function addPlaybook(ValidPlaybook $request)
    {
        $data = $request->all();
        $data['group_id'] = Auth::user()->group_id;

        Log::debug($data);

        $contacts_playbook = ContactsPlaybook::create($data);

        return ['status' => 'success'];
    }

    /**
     * Update a playbook
     * 
     * @param ValidPlaybook $request 
     * @return string[] 
     */
    public function updatePlaybook(ValidPlaybook $request, ContactsPlaybook $contacts_playbook)
    {
        $this->checkPlaybookGroup($contacts_playbook);

        $contacts_playbook->update($request->all());

        return ['status' => 'success'];
    }

    /**
     * Delete a playbook
     * 
     * @param Request $request 
     * @return string[] 
     */
    public function deletePlaybook(ContactsPlaybook $contacts_playbook)
    {
        $this->checkPlaybookGroup($contacts_playbook);

        $contacts_playbook->delete();

        return ['status' => 'success'];
    }

    /**
     * Get a playbook (ajax)
     * 
     * @param Request $request 
     * @return mixed 
     */
    public function getPlaybook(ContactsPlaybook $contacts_playbook)
    {
        $this->checkPlaybookGroup($contacts_playbook);

        $playbook = $contacts_playbook->toArray();

        $playbook['extra_campaigns'] = $contacts_playbook->playbook_campaigns
            ->sortBy('campaign')
            ->pluck('campaign');

        $playbook['subcampaigns'] = $contacts_playbook->playbook_subcampaigns
            ->sortBy('subcampaign')
            ->pluck('subcampaign');

        return $playbook;
    }

    private function checkPlaybookGroup($contacts_playbook)
    {
        if ($contacts_playbook->group_id !== Auth::user()->group_id) {
            abort(403, 'Unauthorized');
        }
    }

    /**
     * Toggle active status
     * 
     * @param Request $request 
     * @return string[] 
     * @throws HttpResponseException 
     */
    public function toggleActive(Request $request)
    {
        $contacts_playbook = ContactsPlaybook::where('id', $request->id)
            ->where('group_id', Auth::user()->group_id)
            ->firstOrFail();

        if (!$this->updateActive($contacts_playbook, $request->checked)) {
            abort(response()->json(['errors' => ['1' => trans('tools.playbook_cant_activate')]], 422));
        }

        return ['status' => 'success'];
    }

    private function updateActive(ContactsPlaybook $contacts_playbook, $active)
    {
        $this->checkPlaybookGroup($contacts_playbook);

        if ($active && !$contacts_playbook->allowActive()) {
            return false;
        }

        // Set active
        $contacts_playbook->active = $active;

        // If activating, reset run dates
        if ($contacts_playbook->active == 1) {
            $contacts_playbook->last_run_from = now();
            $contacts_playbook->last_run_to = $contacts_playbook->last_run_from;
        }

        $contacts_playbook->save();

        return true;
    }

    /**
     * Activate all playbooks
     * 
     * @param Request $request 
     * @return (string|array)[]|string[] 
     */
    public function activateAllPlaybooks(Request $request)
    {
        // get all inactive playbooks
        $playbooks = ContactsPlaybook::where('group_id', Auth::user()->group_id)
            ->where('active', 0)
            ->get();

        $ids = [];
        $names = [];
        foreach ($playbooks as $playbook) {
            if (!$this->updateActive($playbook, 1)) {
                $ids[] = $playbook->id;
                $names[] = $playbook->name;
            }
        }

        if (count($ids)) {
            return [
                'status' => 'error',
                'failed' => [
                    'ids' => $ids,
                    'names' => $names,
                ],
            ];
        }

        return ['status' => 'success'];
    }

    /**
     * Deactivate all playbooks
     * 
     * @param Request $request 
     * @return string[] 
     */
    public function deactivateAllPlaybooks(Request $request)
    {
        // get all active playbooks
        $playbooks = ContactsPlaybook::where('group_id', Auth::user()->group_id)
            ->where('active', 1)
            ->get();

        foreach ($playbooks as $playbook) {
            $this->updateActive($playbook, 0);
        }

        return ['status' => 'success'];
    }

    /**
     * Opt out of emails
     * 
     * @param Request $request 
     * @return View|Factory 
     */
    public function optOut(Request $request)
    {
        PlaybookOptout::firstOrCreate([
            'group_id' => $request->group_id,
            'email' => $request->email
        ]);

        return view('playbook.unsubscribed');
    }
}
