<?php

namespace App\Http\Requests;

use App\Rules\ValidRuleFilters;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Traits\CampaignTraits;
use App\Traits\SqlServerTraits;

class LeadFilter extends FormRequest
{
    use CampaignTraits;
    use SqlServerTraits;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // strip out any filters with null or blank values
        if ($this->has('filters')) {
            if (is_array($this->filters)) {
                $filters = [];
                foreach ($this->filters as $key => $val) {
                    if (trim($val) !== '') {
                        $filters[$key] = $val;
                    }
                }
                $this->merge(['filters' => $filters]);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $group_id = Auth::user()->group_id;

        // This validation is used for both add and update, so get the
        // id of the rule being edited, or set to 0 for an add
        $id = request('id', 0);

        return [
            'rule_name' => [
                'required',
                Rule::unique('lead_rules')->where(function ($query) use ($group_id, $id) {
                    return $query
                        ->where('group_id', $group_id)
                        ->whereNull('deleted_at')
                        ->where('id', '!=', $id);
                }),
            ],
            'source_campaign' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!in_array($value, array_values($this->getAllCampaigns()))) {

                        $fail(trans('custom_validation.source_campaign_invalid'));
                    }
                },
            ],
            'source_subcampaign' => 'nullable',
            'filters' => [
                'required',
                'array',
                'min:1',
                new ValidRuleFilters(),
            ],
            'destination_campaign' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!in_array($value, array_values($this->getAllCampaigns()))) {

                        $fail(trans('custom_validation.destination_campaign_invalid'));
                    }
                },
            ],
            'destination_subcampaign' => 'nullable',
            'description' => 'nullable',
        ];
    }

    public function withValidator($validator)
    {
        // check that source/destination camp/subcamps don't match
        $validator->after(function ($validator) {
            if (request('source_campaign') == request('destination_campaign')) {
                if (
                    (is_null(request('source_subcampaign')) && is_null(request('destination_subcampaign'))) ||
                    request('source_subcampaign') == request('destination_subcampaign')
                ) {
                    $validator->errors()->add('destination_campaign', trans('custom_validation.same_source_destination'));
                }
            }
        });
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'filters.required' => trans('tools.filters_required'),
            'filters.array' => trans('tools.filters_required'),
            'filters.min' => trans('tools.filters_required'),
        ];
    }
}
