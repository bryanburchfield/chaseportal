<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Traits\CampaignTraits;
use App\Traits\SqlServerTraits;

class AddLeadFilterRule extends FormRequest
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
                        ->where('id', '!=', $id);
                }),
            ],
            'source_campaign' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!in_array($value, array_values($this->getAllCampaigns()))) {
                        $fail('Source campaign is invalid.');
                    }
                },
            ],
            'source_subcampaign' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $campaign = request('source_campaign');
                    if (!in_array($value, array_values($this->getAllSubcampaigns($campaign)))) {
                        $fail('Source subcampaign is invalid.');
                    }
                },
            ],
            'filter_type' => [
                'required',
                Rule::in(['lead_age', 'lead_attempts', 'days_called']),
            ],
            'destination_campaign' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!in_array($value, array_values($this->getAllCampaigns()))) {
                        $fail('Destination campaign is invalid.');
                    }
                },
            ],
            'destination_subcampaign' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $campaign = request('destination_campaign');
                    if (!in_array($value, array_values($this->getAllSubcampaigns($campaign)))) {
                        $fail('Destination subcampaign is invalid.');
                    }
                },
            ],
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
                    $validator->errors()->add('destination_campaign', 'Source and Destination campaign/subcampaign must be different');
                }
            }
        });
    }
}
