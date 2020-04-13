<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidPlaybookFilter;
use App\Models\Campaign;
use App\Models\PlaybookFilter;
use App\Traits\CampaignTraits;
use App\Traits\SqlServerTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
            'menuitem' => 'tools',
            'type' => 'other',
        ];

        $data = [
            'page' => $page,
            'jsfile' => ['playbook_filters.js'],
            'group_id' => Auth::user()->group_id,
            'campaigns' => $this->getAllCampaigns(),
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

    public function getFilter(Request $request)
    {
        return $this->findPlaybookFilter($request->id);
    }

    /**
     * Return fields of the Custom Table tied to a campaign
     * 
     * @param Request $request 
     * @return array|mixed 
     */
    public function getTableFields(Request $request)
    {
        $campaign = Campaign::where('CampaignName', $request->campaign)
            ->where('GroupId', Auth::user()->group_id)
            ->first();

        $fields = $this->defaultLeadFields();

        if ($campaign->advancedTable) {
            foreach ($campaign->advancedTable->advancedTableFields as $field) {
                $fields[$field->FieldName] = $field->fieldType->Type;
            }
        }

        return $fields;
    }

    public function addFilter(ValidPlaybookFilter $request)
    {
        $data = $request->all();
        $data['group_id'] = Auth::user()->group_id;

        PlaybookFilter::create($data);

        return ['status' => 'success'];
    }

    public function updateFilter(ValidPlaybookFilter $request)
    {
        Log::debug('Update');
        Log::debug($request);

        return ['status' => 'success'];
    }

    public function deleteFilter(Request $request)
    {
        $playbook_filter = $this->findPlaybookFilter($request->id);
        $playbook_filter->delete();

        return ['status' => 'success'];
    }

    public function getOperators(Request $request)
    {
        $type = $request->has('type') ? $request->type : null;

        return PlaybookFilter::getOperators($type);
    }

    private function findPlaybookfilter($id)
    {
        return PlaybookFilter::where('id', $id)
            ->where('group_id', Auth::user()->group_id)
            ->firstOrFail();
    }
}
