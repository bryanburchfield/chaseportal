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

    private $contacts_playbook;

    public function index(ContactsPlaybook $contacts_playbook)
    {
        $this->setPlaybook($contacts_playbook);

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
            'contacts_playbook' => $this->contacts_playbook,
            'campaigns' => $this->getAllCampaigns(),
            'playbook_touches' => $this->contacts_playbook->playbook_touches->sortBy('name'),
        ];

        return view('playbook.touches')->with($data);
    }

    public function playbookTouchForm(PlaybookTouch $playbook_touch = null)
    {
        $page = [
            'menuitem' => 'playbook',
            'sidenav' => 'main',
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

        return view('playbook.shared.touch_form')->with($data);
    }

    public function addPlaybookTouchForm(ContactsPlaybook $contacts_playbook)
    {
        $this->setPlaybook($contacts_playbook);

        return $this->playbookTouchForm(new PlaybookTouch());
    }

    public function updatePlaybookTouchForm(PlaybookTouch $playbook_touch)
    {
        $this->checkPlaybookGroup($playbook_touch);

        $this->setPlaybook($playbook_touch->contacts_playbook);

        return $this->playbookTouchForm($playbook_touch);
    }

    /**
     * Return playbook if group_id matches user
     * 
     * @param mixed $id 
     * @return mixed 
     */
    private function setPlaybook(ContactsPlaybook $contacts_playbook)
    {
        if ($contacts_playbook->group_id !== Auth::user()->group_id) {
            abort(403, 'Unauthorized');
        }

        $this->contacts_playbook = $contacts_playbook;
    }

    private function findPlaybookTouch($id)
    {
        $playbook_touch = PlaybookTouch::where('id', $id)
            ->with(['contacts_playbook', 'playbook_touch_actions', 'playbook_touch_filters'])
            ->firstOrFail();

        if ($playbook_touch->contacts_playbook->group_id != Auth::user()->group_id) {
            abort(403, 'Unauthorized');
        }

        return $playbook_touch;
    }

    private function checkPlaybookGroup(PlaybookTouch $playbook_touch)
    {
        if ($playbook_touch->contacts_playbook->group_id !== Auth::user()->group_id) {
            abort(403, 'Unauthorized');
        }
    }

    public function addPlaybookTouch(ValidPlaybookTouch $request, ContactsPlaybook $contacts_playbook)
    {
        $this->setPlaybook($contacts_playbook);

        $data = $request->all();
        $data['contacts_playbook_id'] = $this->contacts_playbook->id;
        $data['group_id'] = Auth::user()->group_id;

        DB::beginTransaction();

        $playbook_touch = PlaybookTouch::create($data);

        $playbook_touch->saveFilters($data['filters']);
        $playbook_touch->saveActions($data['actions']);
        $playbook_touch->activate();
        $playbook_touch->save();

        DB::commit();

        return ['status' => 'success'];
    }

    public function updatePlaybookTouch(ValidPlaybookTouch $request, PlaybookTouch $playbook_touch)
    {
        $this->checkPlaybookGroup($playbook_touch);

        $data = $request->all();

        DB::beginTransaction();

        $playbook_touch->update($data);

        $playbook_touch->saveFilters($data['filters']);
        $playbook_touch->saveActions($data['actions']);

        DB::commit();

        // just in case
        if (!$playbook_touch->allowActive()) {
            $playbook_touch->active = 0;
            $playbook_touch->save();

            $this->contacts_playbook->active = 0;
            $this->contacts_playbook->save();
        }

        return ['status' => 'success'];
    }

    public function deletePlaybookTouch(PlaybookTouch $playbook_touch)
    {
        $this->checkPlaybookGroup($playbook_touch);

        $playbook_touch->delete();

        return ['status' => 'success'];
    }

    public function getPlaybookTouch(PlaybookTouch $playbook_touch)
    {
        $this->checkPlaybookGroup($playbook_touch);

        return $playbook_touch;
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

        $related_campaigns = [];

        if (!empty($campaign)) {
            $related_campaigns = (new PlaybookController)->relatedCampaigns($campaign);
        }

        return PlaybookFilter::where('group_id', Auth::user()->group_id)
            ->where(function ($q) use ($campaign, $related_campaigns) {
                $q->where('campaign', $campaign)
                    ->orWhereNull('campaign')
                    ->orWhereIn('campaign', $related_campaigns);
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

        $related_campaigns = [];

        if (!empty($campaign)) {
            $related_campaigns = (new PlaybookController)->relatedCampaigns($campaign);
        }

        return PlaybookAction::where('group_id', Auth::user()->group_id)
            ->where(function ($q) use ($campaign, $related_campaigns) {
                $q->where('campaign', $campaign)
                    ->orWhereNull('campaign')
                    ->orWhereIn('campaign', $related_campaigns);
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

        if ($active) {
            return $playbook_touch->activate();
        }

        return $playbook_touch->deactivate();
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
            ->where('contacts_playbook_id', $this->contacts_playbook->id)
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
            ->where('contacts_playbook_id', $this->contacts_playbook->id)
            ->where('active', 1)
            ->get();

        foreach ($playbook_touchess as $playbook_touch) {
            $this->updateActive($playbook_touch->id, 0);
        }

        return ['status' => 'success'];
    }
}
