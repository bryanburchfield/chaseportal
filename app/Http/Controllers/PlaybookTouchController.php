<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidPlaybookTouch;
use App\Http\Requests\ValidPlaybookTouchAction;
use App\Http\Requests\ValidPlaybookTouchFilter;
use App\Models\ContactsPlaybook;
use App\Models\PlaybookAction;
use App\Models\PlaybookFilter;
use App\Models\PlaybookTouch;
use App\Traits\CampaignTraits;
use App\Traits\SqlServerTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PlaybookTouchController extends Controller
{
    use CampaignTraits;
    use SqlServerTraits;

    private $contacts_playbook_id;
    private $contacts_playbook;

    public function __construct(Request $request)
    {
        $this->contacts_playbook_id = $request->contacts_playbook_id;
        $this->id = $request->id;
    }

    public function index()
    {
        $this->setPlaybook($this->contacts_playbook_id);

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
            'contacts_playbook' => $this->contacts_playbook,
            'campaigns' => $this->getAllCampaigns(),
            'playbook_touches' => $this->getPlaybookTouches(),
        ];

        return view('tools.playbook.touches')->with($data);
    }

    public function playbookTouchForm(PlaybookTouch $playbook_touch = null)
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
            'contacts_playbook' => $this->contacts_playbook,
            'playbook_touch' => $playbook_touch,
            'playbook_filters' => $this->filters(),
            'playbook_actions' => $this->actions(),
        ];

        return view('tools.playbook.shared.touch_form')->with($data);
    }

    public function addPlaybookTouchForm()
    {
        $this->setPlaybook($this->contacts_playbook_id);

        return $this->playbookTouchForm(new PlaybookTouch());
    }

    public function updatePlaybookTouchForm()
    {
        $playbook_touch = $this->findPlaybookTouch($this->id);
        $this->setPlaybook($playbook_touch->contacts_playbook_id);

        return $this->playbookTouchForm($playbook_touch);
    }

    /**
     * Return playbook if group_id matches user
     * 
     * @param mixed $id 
     * @return mixed 
     */
    private function setPlaybook($id)
    {
        $this->contacts_playbook = ContactsPlaybook::where('id', $id)
            ->where('group_id', Auth::user()->group_id)
            ->firstOrFail();
    }

    private function getPlaybookTouches()
    {
        return PlaybookTouch::where('contacts_playbook_id', $this->contacts_playbook_id)
            ->orderBy('name')
            ->get();
    }

    private function findPlaybookTouch($id)
    {
        $playbook_touch = PlaybookTouch::where('id', $id)
            ->with(['contacts_playbook', 'playbook_touch_actions', 'playbook_touch_filters'])
            ->firstOrFail();

        if ($playbook_touch->contacts_playbook->group_id != Auth::user()->group_id) {
            abort(404);
        }

        return $playbook_touch;
    }

    public function addPlaybookTouch(ValidPlaybookTouch $request)
    {
        $this->setPlaybook($this->contacts_playbook_id);

        $data = $request->all();
        $data['contacts_playbook_id'] = $this->contacts_playbook_id;
        $data['group_id'] = Auth::user()->group_id;

        DB::beginTransaction();

        $playbook_touch = PlaybookTouch::create($data);

        $playbook_touch->saveFilters($data['filters']);
        $playbook_touch->saveActions($data['actions']);

        DB::commit();

        return ['status' => 'success'];
    }

    public function updatePlaybookTouch(ValidPlaybookTouch $request)
    {
        $this->setPlaybook($this->contacts_playbook_id);

        $playbook_touch = $this->findPlaybookTouch($request->id);

        $data = $request->all();

        DB::beginTransaction();

        $playbook_touch->update($request->all());

        $playbook_touch->saveFilters($data['filters']);
        $playbook_touch->saveActions($data['actions']);

        DB::commit();

        return ['status' => 'success'];
    }

    public function deletePlaybookTouch(Request $request)
    {
        $this->setPlaybook($this->contacts_playbook_id);

        $playbook_touch = $this->findPlaybookTouch($request->id);
        $playbook_touch->delete();

        return ['status' => 'success'];
    }

    public function getPlaybookTouch(Request $request)
    {
        return $this->findPlaybookTouch($request->id);
    }

    /**
     * Get filters of a touch (ajax)
     * 
     * @param Request $request 
     * @return Collection 
     * @throws InvalidArgumentException 
     * @throws RuntimeException 
     */
    public function getPlaybookTouchFilters(Request $request)
    {
        $playbook_touch = $this->findPlaybookTouch($request->id);

        return DB::table('playbook_touch_filters')
            ->join('playbook_filters', 'playbook_touch_filters.playbook_filter_id', '=', 'playbook_filters.id')
            ->where('playbook_touch_id', $playbook_touch->id)
            ->select(
                'playbook_touch_filters.id as playbook_touch_filter_id',
                'playbook_touch_filters.playbook_filter_id',
                'playbook_filters.name'
            )
            ->orderBy('playbook_filters.name')
            ->get();
    }

    private function actions()
    {
        $request = new Request();
        if (!empty($this->contacts_playbook->campaign)) {
            $request->merge(['campaign' => $this->contacts_playbook->campaign]);
        }

        return $this->getActions($request);
    }

    private function filters()
    {
        $request = new Request();
        if (!empty($this->contacts_playbook->campaign)) {
            $request->merge(['campaign' => $this->contacts_playbook->campaign]);
        }

        return $this->getFilters($request);
    }

    /**
     * Get actions of a touch (ajax)
     * 
     * @param Request $request 
     * @return Collection 
     * @throws InvalidArgumentException 
     * @throws RuntimeException 
     */
    public function getPlaybookTouchActions(Request $request)
    {
        $playbook_touch = $this->findPlaybookTouch($request->id);

        return DB::table('playbook_touch_actions')
            ->join('playbook_actions', 'playbook_touch_actions.playbook_action_id', '=', 'playbook_actions.id')
            ->where('playbook_touch_id', $playbook_touch->id)
            ->select(
                'playbook_touch_actions.id as playbook_touch_action_id',
                'playbook_touch_actions.playbook_action_id',
                'playbook_actions.name'
            )
            ->orderBy('playbook_actions.name')
            ->get();
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

    public function saveFilters(ValidPlaybookTouchFilter $request)
    {
        $playbook_touch = $this->findPlaybookTouch($request->id);
        $playbook_touch->saveFilters($request->filters);

        return ['status' => 'success'];
    }

    public function saveActions(ValidPlaybookTouchAction $request)
    {
        $playbook_touch = $this->findPlaybookTouch($request->id);
        $playbook_touch->saveActions($request->actions);

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
            abort(response()->json(['errors' => ['1' => trans('tools.playbook_touch_cant_activate')]], 422));
        }

        return ['status' => 'success'];
    }

    private function updateActive($id, $active)
    {
        $playbook_touch = $this->findPlaybookTouch($id);

        if ($active && !$playbook_touch->allowActive()) {
            return false;
        }

        // Set active
        $playbook_touch->active = $active;
        $playbook_touch->save();

        return true;
    }

    /**
     * Activate all touches
     * 
     * @param Request $request 
     * @return (string|array)[]|string[] 
     */
    public function activateAllPlaybookTouches(Request $request)
    {
        // get all inactive playbooks
        $playbook_touches = PlaybookTouch::where('group_id', Auth::user()->group_id)
            ->where('contacts_playbook_id', $this->contacts_playbook_id)
            ->where('active', 0)
            ->get();

        $ids = [];
        $names = [];
        foreach ($playbook_touches as $playbook_touch) {
            if (!$this->updateActive($playbook_touch->id, 1)) {
                $ids[] = $playbook_touch->id;
                $names[] = $playbook_touch->name;
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
     * Deactivate all touches
     * 
     * @param Request $request 
     * @return string[] 
     */
    public function deactivateAllPlaybookTouchess(Request $request)
    {
        // get all active touches
        $playbook_touchess = PlaybookTouch::where('group_id', Auth::user()->group_id)
            ->where('contacts_playbook_id', $this->contacts_playbook_id)
            ->where('active', 1)
            ->get();

        foreach ($playbook_touchess as $playbook_touch) {
            $this->updateActive($playbook_touch->id, 0);
        }

        return ['status' => 'success'];
    }
}
