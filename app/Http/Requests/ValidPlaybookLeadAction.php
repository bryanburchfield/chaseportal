<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidPlaybookLeadAction extends FormRequest
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
        return [
            'to_campaign' => 'nullable',
            'to_subcampaign' => 'nullable',
            'to_callstatus' => 'nullable',
        ];
    }

    public function withValidator($validator)
    {
        // check that something was filled in
        $validator->after(function ($validator) {
            if (
                empty(request('to_campaign')) &&
                empty(request('to_subcampaign')) &&
                empty(request('to_callstatus'))
            ) {

                $validator->errors()->add('to_campaign', trans('custom_validation.lead_update_empty'));
            }
        });
    }
}
