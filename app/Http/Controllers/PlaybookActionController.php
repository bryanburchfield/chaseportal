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
use Illuminate\Support\Facades\Log;

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
        return $this->findPlaybookAction($request->id);
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
        switch ($request->action_type) {
            case 'email':
                app(ValidPlaybookEmailAction::class);
                break;
            case 'sms':
                app(ValidPlaybookSmsAction::class);
                break;
            case 'lead':
                app(ValidPlaybookLeadAction::class);
                break;
        }

        $data = $request->all();
        $data['group_id'] = Auth::user()->group_id;

        // use transaction since we're inserting 2 records
        DB::beginTransaction();

        $playbook_action = PlaybookAction::create($data);

        $data['playbook_action_id'] = $playbook_action->id;

        switch ($request->action_type) {
            case 'email':
                PlaybookEmailAction::create($data);
                break;
            case 'sms':
                PlaybookSmsAction::create($data);
                break;
            case 'lead':
                PlaybookLeadAction::create($data);
                break;
        }

        DB::commit();

        return ['status' => 'success'];
    }

    public function updateAction(Request $request)
    {
        Log::debug('add');
        Log::debug($request->all());

        $playbook_action = $this->findPlaybookAction($request->id);

        // update stuff

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
}
