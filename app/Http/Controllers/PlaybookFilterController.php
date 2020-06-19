<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidPlaybookFilter;
use App\Models\Campaign;
use App\Models\PlaybookFilter;
use App\Traits\CampaignTraits;
use App\Traits\SqlServerTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlaybookFilterController extends Controller
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
            'jsfile' => ['playbook_filters.js'],
            'cssfile' => ['https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css'],
            'group_id' => Auth::user()->group_id,
            'campaigns' => $this->getAllCampaigns(),
            'fields' => (new Campaign)->getFilterFields(true),
            'playbook_filters' => $this->getPlaybookFilters(),
        ];

        return view('tools.playbook.filters')->with($data);
    }

    /**
     * Filters configured for this group
     * 
     * @return mixed 
     */
    private function getPlaybookFilters()
    {
        return PlaybookFilter::where('group_id', Auth::User()->group_id)
            ->orderBy('name')
            ->get();
    }

    /**
     * Return a single playbook_filters record by id
     * 
     * @param Request $request 
     * @return mixed 
     */
    public function getFilter(Request $request)
    {
        return $this->findPlaybookFilter($request->id);
    }

    /**
     * Return availble fields for a filter
     * 
     * @param Request $request 
     * @return array|mixed 
     */
    public function getFilterFields(Request $request)
    {
        if ($request->has('campaign')) {
            $campaign = Campaign::where('GroupId', Auth::user()->group_id)
                ->where('CampaignName', $request->campaign)
                ->with('advancedTable.advancedTableFields.fieldType')
                ->first();
        } else {
            $campaign = new Campaign;
        }

        return $campaign->getFilterFields(true);
    }

    /**
     * Create new playbook_filters record
     * 
     * @param ValidPlaybookFilter $request 
     * @return string[] 
     */
    public function addFilter(ValidPlaybookFilter $request)
    {
        $data = $request->all();
        $data['group_id'] = Auth::user()->group_id;

        PlaybookFilter::create($data);

        return ['status' => 'success'];
    }

    /**
     * Update a playbook_filters record
     * 
     * @param ValidPlaybookFilter $request 
     * @return string[] 
     */
    public function updateFilter(ValidPlaybookFilter $request)
    {
        $playbook_filter = $this->findPlaybookFilter($request->id);
        $playbook_filter->update($request->all());

        return ['status' => 'success'];
    }

    /**
     * Delete a playbook_filters record
     * 
     * @param Request $request 
     * @return string[] 
     */
    public function deleteFilter(Request $request)
    {
        $playbook_filter = $this->findPlaybookFilter($request->id);

        if ($playbook_filter->playbook_touch_filters->isNotEmpty()) {
            abort(response()->json(['errors' => ['1' => trans('tools.filter_in_use')]], 422));
        }

        $playbook_filter->delete();

        return ['status' => 'success'];
    }

    /**
     * Return a list of availabe operators
     * 
     * @param Request $request 
     * @return array 
     */
    public function getOperators(Request $request)
    {
        $type = $request->has('type') ? $request->type : null;

        return PlaybookFilter::getOperators($type);
    }

    /**
     * Find plabook_filters record by id for user's group
     * @param mixed $id 
     * @return mixed 
     */
    private function findPlaybookFilter($id)
    {
        return PlaybookFilter::where('id', $id)
            ->where('group_id', Auth::user()->group_id)
            ->firstOrFail();
    }
}
