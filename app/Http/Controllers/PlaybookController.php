<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidContactsPlaybookAction;
use App\Http\Requests\ValidContactsPlaybookFilter;
use App\Http\Requests\ValidPlaybook;
use App\Models\ContactsPlaybook;
use App\Models\PlaybookAction;
use App\Models\PlaybookFilter;
use App\Traits\CampaignTraits;
use App\Traits\SqlServerTraits;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

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
            'cssfile' => ['https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css'],
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

        ContactsPlaybook::create($data);

        return ['status' => 'success'];
    }

    /**
     * Update a playbook
     * 
     * @param ValidPlaybook $request 
     * @return string[] 
     */
    public function updatePlaybook(ValidPlaybook $request)
    {
        $contacts_playbook = $this->findPlaybook($request->id);
        $contacts_playbook->update($request->all());

        return ['status' => 'success'];
    }

    /**
     * Delete a playbook
     * 
     * @param Request $request 
     * @return string[] 
     */
    public function deletePlaybook(Request $request)
    {
        $contacts_playbook = $this->findPlaybook($request->id);
        $contacts_playbook->delete();

        return ['status' => 'success'];
    }

    /**
     * Get a playbook (ajax)
     * 
     * @param Request $request 
     * @return mixed 
     */
    public function getPlaybook(Request $request)
    {
        return $this->findPlaybook($request->id);
    }

    /**
     * Get filters of a playbook (ajax)
     * 
     * @param Request $request 
     * @return Collection 
     * @throws InvalidArgumentException 
     * @throws RuntimeException 
     */
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

    /**
     * Get actions of a playbook (ajax)
     * 
     * @param Request $request 
     * @return Collection 
     * @throws InvalidArgumentException 
     * @throws RuntimeException 
     */
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

    /**
     * Return playbook if group_id matches user
     * 
     * @param mixed $id 
     * @return mixed 
     */
    private function findPlaybook($id)
    {
        return ContactsPlaybook::where('id', $id)
            ->where('group_id', Auth::user()->group_id)
            ->firstOrFail();
    }

    /**
     * Get all available filters for a campaign
     * 
     * @param Request $request 
     * @return mixed 
     */
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

    /**
     * Get all available actions for a campaign
     * 
     * @param Request $request 
     * @return mixed 
     */
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

    /**
     *  Save filters on a playbook
     * 
     * @param ValidContactsPlaybookFilter $request 
     * @return string[] 
     */
    public function saveFilters(ValidContactsPlaybookFilter $request)
    {
        $contacts_playbook = ContactsPlaybook::findOrFail($request->id);
        $contacts_playbook->saveFilters($request->filters);

        return ['status' => 'success'];
    }

    /**
     * Save actions on a playbook
     * 
     * @param ValidContactsPlaybookAction $request 
     * @return string[] 
     */
    public function saveActions(ValidContactsPlaybookAction $request)
    {
        $contacts_playbook = ContactsPlaybook::findOrFail($request->id);
        $contacts_playbook->saveActions($request->actions);

        return ['status' => 'success'];
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
        if (!$this->updateActive($request->id, $request->checked)) {
            abort(response()->json(['errors' => ['1' => trans('tools.playbook_cant_activate')]], 422));
        }

        return ['status' => 'success'];
    }

    private function updateActive($id, $active)
    {
        $contacts_playbook = $this->findPlaybook($id);

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
            if (!$this->updateActive($playbook->id, 1)) {
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
            $this->updateActive($playbook->id, 0);
        }

        return ['status' => 'success'];
    }
}
