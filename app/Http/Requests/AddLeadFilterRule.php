<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AddLeadFilterRule extends FormRequest
{
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

        return [
            'rule_name' => [
                'required',
                Rule::unique('lead_rules')->where(function ($query) use ($group_id) {
                    return $query
                        ->where('group_id', $group_id);
                }),
            ],
            'source_campaign' => [
                'required',
                // exists in sqlsrv
            ],
            'source_subcampaign' => [
                'nullable',
                // exists in sqlsrv
            ],
            'filter_type' => [
                'required',
                Rule::in(['lead_age', 'lead_attempts', 'days_called']),
            ],
            'destination_campaign' => [
                'required',
                // exists in sqlsrv
            ],
            'destination_subcampaign' => [
                'nullable',
                // exists in sqlsrv
            ],
            'description' => 'nullable',
        ];
    }
}
