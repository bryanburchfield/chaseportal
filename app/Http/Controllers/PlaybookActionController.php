<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidPlaybookAction;
use App\Http\Requests\ValidPlaybookEmailAction;
use App\Http\Requests\ValidPlaybookLeadAction;
use App\Http\Requests\ValidPlaybookSmsAction;
use App\Models\Campaign;
use App\Models\Dispo;
use App\Models\EmailServiceProvider;
use App\Models\PlaybookAction;
use App\Models\PlaybookEmailAction;
use App\Models\PlaybookLeadAction;
use App\Models\PlaybookSmsAction;
use App\Models\PlaybookTouchAction;
use App\Models\SmsFromNumber;
use App\Models\Script;
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
            'menuitem' => 'playbook',
            'sidenav' => 'main',
            'type' => 'other',
        ];

        $data = [
            'page' => $page,
            'jsfile' => ['playbook_actions.js'],
            'cssfile' => ['https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css', 'https://cdn.datatables.net/fixedheader/3.1.7/css/fixedHeader.dataTables.min.css'],
            'group_id' => Auth::user()->group_id,
            'campaigns' => $this->getAllCampaigns(),
            'playbook_actions' => $this->getPlaybookActions(),
            'email_service_providers' => $this->getEmailServiceProviders(),
            'email_templates' => Script::emailTemplates(),
            'sms_templates' => Script::smsTemplates(),
            'sms_from_numbers' => $this->smsFromNumbers(),
        ];

        return view('playbook.actions')->with($data);
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
    public function getAction(PlaybookAction $playbook_action)
    {
        $this->checkActionGroup($playbook_action);

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

    private function checkActionGroup(PlaybookAction $playbook_action)
    {
        if ($playbook_action->group_id !== Auth::user()->group_id) {
            abort(403, 'Unauthorized');
        }
    }

    /**
     * Add an action
     * 
     * @param ValidPlaybookAction $request 
     * @return string[] 
     */
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

    /**
     * Update an action
     * 
     * @param ValidPlaybookAction $request 
     * @return string[] 
     */
    public function updateAction(ValidPlaybookAction $request, PlaybookAction $playbook_action)
    {
        // first, make sure it's the correct group
        $this->checkActionGroup($playbook_action);

        // validate fields based on action_type
        $model = $this->validateActionType($request);

        $data = $request->all();

        // transaction since we're doing a bunch of updates/deletes/inserts
        DB::beginTransaction();

        // If action is of type lead and has been run, we need to
        // (soft) delete and insert as a new action so history
        // is preserved.
        if ($playbook_action->action_type == 'lead' && $playbook_action->playbook_run_touch_actions()->exists()) {
            $old_touches = $playbook_action->playbook_touch_actions;

            $playbook_action->delete();
            $playbook_action = PlaybookAction::create($data);

            // Now insert any playbook touch actions that used to be linked to deleted action
            foreach ($old_touches as $touch) {
                PlaybookTouchAction::create([
                    'playbook_touch_id' => $touch->playbook_touch_id,
                    'playbook_action_id' => $playbook_action->id,
                ]);
            }
        } else {
            $playbook_action->update($data);
        }

        // delete any off-type actions - use find/delete so audit trail & soft delete works
        if ($data['action_type'] != 'email') {
            $subaction = PlaybookEmailAction::where('playbook_action_id', $data['id'])->first();
            if ($subaction) {
                $subaction->delete();
            }
        }
        if ($data['action_type'] != 'sms') {
            $subaction = PlaybookSmsAction::where('playbook_action_id', $data['id'])->first();
            if ($subaction) {
                $subaction->delete();
            }
        }
        if ($data['action_type'] != 'lead') {
            $subaction = PlaybookLeadAction::where('playbook_action_id', $data['id'])->first();
            if ($subaction) {
                $subaction->delete();
            }
        }

        // update/create action type
        $model::updateOrCreate(
            ['playbook_action_id' => $playbook_action->id],
            $data
        );

        DB::commit();

        return ['status' => 'success'];
    }

    /**
     * Delete an action
     * 
     * @param Request $request 
     * @return string[] 
     * @throws HttpResponseException 
     */
    public function deleteAction(PlaybookAction $playbook_action)
    {
        // on delete cascade takes care of the sub-table records
        $this->checkActionGroup($playbook_action);

        if ($playbook_action->playbook_touch_actions->isNotEmpty()) {
            abort(response()->json(['errors' => ['1' => trans('action_in_use')]], 422));
        }

        $playbook_action->delete();

        return ['status' => 'success'];
    }

    /**
     * Get available Dispos
     * 
     * @param Request $request 
     * @return mixed|array 
     */
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
        $results = $this->getAllSubcampaigns($request->campaign);

        return ['subcampaigns' => $results];
    }

    /**
     * Return custom table fields
     * 
     * @param Request $request 
     * @return array|mixed 
     */
    public function getTableFields(Request $request)
    {
        $fields = [];

        if ($request->has('campaign')) {
            $campaign = Campaign::where('CampaignName', $request->campaign)
                ->where('GroupId', Auth::user()->group_id)
                ->with('advancedTable.advancedTableFields.fieldType')
                ->first();

            if ($campaign && $campaign->advancedTable) {
                foreach ($campaign->advancedTable->advancedTableFields as $field) {
                    $fields[$field->FieldName] = $field->fieldType->Type;
                }
            }
        }

        return $fields;
    }

    /**
     * Validate and action based on its type
     * 
     * @param Request $request 
     * @return mixed|string 
     * @throws BindingResolutionException 
     */
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

    /**
     * Servers configured for this group
     * 
     * @return mixed 
     */
    private function getEmailServiceProviders()
    {
        return EmailServiceProvider::where('group_id', Auth::User()->group_id)
            ->orderBy('name')
            ->get();
    }

    /**
     * Return SMS from numbers
     * 
     * @return mixed 
     */
    private function smsFromNumbers()
    {
        return SmsFromNumber::whereIn('group_id', [0, Auth::user()->group_id])
            ->select(['id', 'from_number'])
            ->orderBy('from_number')
            ->get();
    }
}
