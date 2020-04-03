<?php

namespace App\Http\Controllers;

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
            'menuitem' => 'tools',
            'type' => 'other',
        ];

        $data = [
            'page' => $page,
            'jsfile' => ['playbook_filters.js'],
            'group_id' => Auth::user()->group_id,
            'campaigns' => $this->getAllCampaigns(),
            'operators' => $this->getOperators(),
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
     * Return list of operators for filters
     * 
     * @param bool $detail 
     * @return array 
     */
    private function getOperators($detail = false)
    {
        $mathops_detail = [
            '=' => [
                'description' => trans('tools.equals'),
                'allow_nulls' => false,
                'value_type' => 'string',
            ],
            '!=' => [
                'description' => trans('tools.not_equals'),
                'allow_nulls' => false,
                'value_type' => 'string',
            ],
            '<' => [
                'description' => trans('tools.less_than'),
                'allow_nulls' => false,
                'value_type' => 'string',
            ],
            '>' => [
                'description' => trans('tools.greater_than'),
                'allow_nulls' => false,
                'value_type' => 'string',
            ],
            '<=' => [
                'description' => trans('tools.less_than_or_equals'),
                'allow_nulls' => false,
                'value_type' => 'string',
            ],
            '>=' => [
                'description' => trans('tools.greater_than_or_equals'),
                'allow_nulls' => false,
                'value_type' => 'string',
            ],
            'blank' => [
                'description' => trans('tools.is_blank'),
                'allow_nulls' => true,
                'value_type' => null,
            ],
            'not_blank' => [
                'description' => trans('tools.is_not_blank'),
                'allow_nulls' => true,
                'value_type' => null,
            ],
        ];

        $dateops_detail = [
            'days_ago' => [
                'description' => trans('tools.days_ago'),
                'allow_nulls' => false,
                'value_type' => 'integer',
            ],
            'days_from_now' => [
                'description' => trans('tools.days_from_now'),
                'allow_nulls' => false,
                'value_type' => 'integer',
            ],
            '<_days_ago' => [
                'description' => trans('tools.less_than_days_ago'),
                'allow_nulls' => false,
                'value_type' => 'integer',
            ],
            '>_days_ago' => [
                'description' => trans('tools.greater_than_days_ago'),
                'allow_nulls' => false,
                'value_type' => 'integer',
            ],
            '<_days_from_now' => [
                'description' => trans('tools.less_than_days_from_now'),
                'allow_nulls' => false,
                'value_type' => 'integer',
            ],
            '>_days_from_now' => [
                'description' => trans('tools.greater_than_days_from_now'),
                'allow_nulls' => false,
                'value_type' => 'integer',
            ],
        ];

        // there's a better way to do this
        if ($detail) {
            $mathops = $mathops_detail;
            $dateops = $dateops_detail;
        } else {
            $mathops = [];
            $dateops = [];
            foreach ($mathops_detail as $key => $array) {
                $mathops[$key] = $array['description'];
            }
            foreach ($dateops_detail as $key => $array) {
                $dateops[$key] = $array['description'];
            }
        }

        return [
            'integer' => $mathops,
            'string' => $mathops,
            'date' => array_merge($mathops, $dateops),
            'text' => $mathops,
            'phone' => $mathops,
        ];
    }

    /**
     * Return all string fields of the Custom Table tied to a campaign
     * 
     * @param Request $request 
     * @return array|mixed 
     */
    public function getTableFields(Request $request)
    {
        $campaign = Campaign::where('CampaignName', $request->campaign)
            ->where('GroupId', Auth::user()->group_id)
            ->first();

        $fields = [];
        foreach ($campaign->advancedTable->advancedTableFields as $field) {
            // only return 2:string, 4:text, 5:phone
            if (
                $field->fieldType->id == 2 ||
                $field->fieldType->id == 4 ||
                $field->fieldType->id == 5
            ) {
                $fields[$field->FieldName] = $field->fieldType->Type;
            }
        }

        return $fields;
    }
}
