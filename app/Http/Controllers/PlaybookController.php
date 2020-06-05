<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidPlaybook;
use App\Models\ContactsPlaybook;
use App\Models\PlaybookOptout;
use App\Traits\CampaignTraits;
use App\Traits\SqlServerTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'menu' => 'tools',
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

        return view('tools.playbook.unsubscribed');
    }
}
