<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidPlaybookAction;
use App\Http\Requests\ValidPlaybookEmailAction;
use App\Http\Requests\ValidPlaybookLeadAction;
use App\Http\Requests\ValidPlaybookSmsAction;
use App\Models\Dispo;
use App\Models\PlaybookAction;
use App\Models\PlaybookEmailAction;
use App\Models\PlaybookLeadAction;
use App\Models\PlaybookSmsAction;
use App\Traits\CampaignTraits;
use App\Traits\SqlServerTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PlaybookActionController extends Controller
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
            'jsfile' => ['playbook_actions.js'],
            'group_id' => Auth::user()->group_id,
            'campaigns' => $this->getAllCampaigns(),
            'playbook_actions' => $this->getPlaybookActions(),
        ];

        return view('tools.playbook.actions')->with($data);
    }

    /**
     * Actions configured for this group
     * 
     * @return mixed 
     */
    private function getPlaybookActions()
    {
        return PlaybookAction::where('group_id', Auth::User()->group_id)
            ->orderBy('name')
            ->get();
    }

    /**
     * Return a single playbook_actions record by id
     * 
     * @param Request $request 
     * @return mixed 
     */
    public function getAction(Request $request)
    {
        $playbook_action = $this->findPlaybookAction($request->id);

        switch ($playbook_action->action_type) {
            case 'email':
                $record = PlaybookEmailAction::where('playbook_action_id', $playbook_action->id)->first();
                break;
            case 'sms':
                $record = PlaybookSmsAction::where('playbook_action_id', $playbook_action->id)->first();
                break;
            case 'lead':
                $record = PlaybookLeadAction::where('playbook_action_id', $playbook_action->id)->first();
                break;
        }

        return $playbook_action->toArray() + $record->toArray();
    }

    /**
     * Find plabook_actions record by id for user's group
     * @param mixed $id 
     * @return mixed 
     */
    private function findPlaybookAction($id)
    {
        return PlaybookAction::where('id', $id)
            ->where('group_id', Auth::user()->group_id)
            ->firstOrFail();
    }

    public function addAction(ValidPlaybookAction $request)
    {
        // validate fields based on action_type
        $model = $this->validateActionType($request);

        $data = $request->all();
        $data['group_id'] = Auth::user()->group_id;

        // use transaction since we're inserting 2 records
        DB::beginTransaction();

        $playbook_action = PlaybookAction::create($data);
        $data['playbook_action_id'] = $playbook_action->id;

        $model::create($data);

        DB::commit();

        return ['status' => 'success'];
    }

    public function updateAction(ValidPlaybookAction $request)
    {
        // first, make sure it's the correct group
        $playbook_action = $this->findPlaybookAction($request->id);

        // validate fields based on action_type
        $model = $this->validateActionType($request);

        $data = $request->all();

        // transaction since we're doing a bunch of updates/deletes/inserts
        DB::beginTransaction();

        // update action
        $playbook_action->update($data);

        // delete any off-type actions - use find/delete so audit trail works
        if ($data['action_type'] != 'email') {
            $playbook_email_action = PlaybookEmailAction::where('playbook_action_id', $data['id'])->first();
            $playbook_email_action->delete();
        }
        if ($data['action_type'] != 'sms') {
            $playbook_sms_action = PlaybookSmsAction::where('playbook_action_id', $data['id'])->first();
            $playbook_sms_action->delete();
        }
        if ($data['action_type'] != 'lead') {
            $playbook_lead_action = PlaybookLeadAction::where('playbook_action_id', $data['id'])->first();
            $playbook_lead_action->delete();
        }

        // update/create action type
        $model::updateOrCreate(
            ['playbook_action_id', $data['id']],
            $data,
        );

        DB::commit();

        return ['status' => 'success'];
    }

    public function deleteAction(Request $request)
    {
        // on delete cascade takes care of the sub-table records
        $playbook_action = $this->findPlaybookAction($request->id);
        $playbook_action->delete();

        return ['status' => 'success'];
    }

    public function getDispos(Request $request)
    {
        $campaign = $request->has('campaign') ? $request->campaign : '';

        return resultsToList(Dispo::availableDispos($campaign));
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

    private function getAllSubcampaignsWithNone($campaign)
    {
        $results = $this->getAllSubcampaigns($campaign);
        $results = ['!!none!!' => trans('tools.no_subcampaign')] + $results;

        return $results;
    }

    private function validateActionType(Request $request)
    {
        switch ($request->action_type) {
            case 'email':
                app(ValidPlaybookEmailAction::class);
                $model = PlaybookEmailAction::class;
                break;
            case 'sms':
                app(ValidPlaybookSmsAction::class);
                $model = PlaybookSmsAction::class;
                break;
            case 'lead':
                app(ValidPlaybookLeadAction::class);
                $model = PlaybookLeadAction::class;
                break;
        }

        return $model;
    }
}
