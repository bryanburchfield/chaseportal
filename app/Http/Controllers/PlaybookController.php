<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidContactsPlaybookAction;
use App\Http\Requests\ValidContactsPlaybookFilter;
use App\Http\Requests\ValidPlaybook;
use App\Models\ContactsPlaybook;
use App\Models\ContactsPlaybookAction;
use App\Models\ContactsPlaybookFilter;
use App\Models\PlaybookAction;
use App\Models\PlaybookFilter;
use App\Traits\CampaignTraits;
use App\Traits\SqlServerTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            'jsfile' => ['contacts_playbooks.js'],
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

    public function addPlaybook(ValidPlaybook $request)
    {
        $data = $request->all();
        $data['group_id'] = Auth::user()->group_id;

        ContactsPlaybook::create($data);

        return ['status' => 'success'];
    }

    public function updatePlaybook(ValidPlaybook $request)
    {
        $contacts_playbook = $this->findPlaybook($request->id);
        $contacts_playbook->update($request->all());

        return ['status' => 'success'];
    }

    public function deletePlaybook(Request $request)
    {
        $contacts_playbook = $this->findPlaybook($request->id);
        $contacts_playbook->delete();

        return ['status' => 'success'];
    }

    public function getPlaybook(Request $request)
    {
        return $this->findPlaybook($request->id);
    }

    public function getPlaybookFilters(Request $request)
    {
        $contacts_playbook = $this->findPlaybook($request->id);

        return DB::table('contacts_playbook_filters')
            ->join('playbook_filters', 'contacts_playbook_filters.playbook_filter_id', '=', 'playbook_filters.id')
            ->where('contacts_playbook_id', $contacts_playbook->id)
            ->select(
                'contacts_playbook_filters.id as contacts_playbook_filter_id',
                'contacts_playbook_filters.playbook_filter_id',
                'playbook_filters.name'
            )
            ->orderBy('playbook_filters.name')
            ->get();
    }

    public function getPlaybookActions(Request $request)
    {
        $contacts_playbook = $this->findPlaybook($request->id);

        return DB::table('contacts_playbook_actions')
            ->join('playbook_actions', 'contacts_playbook_actions.playbook_action_id', '=', 'playbook_actions.id')
            ->where('contacts_playbook_id', $contacts_playbook->id)
            ->select(
                'contacts_playbook_actions.id as contacts_playbook_action_id',
                'contacts_playbook_actions.playbook_action_id',
                'playbook_actions.name'
            )
            ->orderBy('playbook_actions.name')
            ->get();
    }

    private function findPlaybook($id)
    {
        return ContactsPlaybook::where('id', $id)
            ->where('group_id', Auth::user()->group_id)
            ->firstOrFail();
    }

    public function getFilters(Request $request)
    {
        $campaign = $request->has('campaign') ? $request->campaign : null;

        return PlaybookFilter::where('group_id', Auth::user()->group_id)
            ->where(function ($q) use ($campaign) {
                $q->where('campaign', $campaign)
                    ->orWhereNull('campaign');
            })
            ->orderBy('name')
            ->get();
    }

    public function getActions(Request $request)
    {
        $campaign = $request->has('campaign') ? $request->campaign : null;

        return PlaybookAction::where('group_id', Auth::user()->group_id)
            ->where(function ($q) use ($campaign) {
                $q->where('campaign', $campaign)
                    ->orWhereNull('campaign');
            })
            ->orderBy('name')
            ->get();
    }

    private function deactivateIfEmpty($id)
    {
        $contacts_playbook = ContactsPlaybook::find($id);

        if (!$contacts_playbook->allowActive()) {
            $contacts_playbook->update(['active' => 0]);
        }
    }

    public function saveFilters(ValidContactsPlaybookFilter $request)
    {
        $contacts_playbook = ContactsPlaybook::findOrFail($request->id);
        $contacts_playbook->saveFilters($request->filters);

        return ['status' => 'success'];
    }

    public function saveActions(ValidContactsPlaybookAction $request)
    {
        $contacts_playbook = ContactsPlaybook::findOrFail($request->id);
        $contacts_playbook->saveActions($request->actions);

        return ['status' => 'success'];
    }

    public function toggleActive(Request $request)
    {
        $contacts_playbook = $this->findPlaybook($request->id);

        // Toggle active
        $contacts_playbook->active = !$contacts_playbook->active;

        // If activating, reset run dates
        if ($contacts_playbook->active == 1) {
            $contacts_playbook->last_run_from = now();
            $contacts_playbook->last_run_to = $contacts_playbook->last_run_from;
        }

        $contacts_playbook->save();

        return ['status' => 'success'];
    }
}
