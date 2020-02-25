<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ValidEmailDripCampaign extends FormRequest
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
        $group_id = Auth::User()->group_id;

        return [
            'name' => 'required',
            'description' => 'required',
            'campaign' => 'required',
            'subcampaign' => 'required|array',
            'subject' => 'required',
            'from' => 'required',
            'email_field' => 'required',
            'email_service_provider_id' => [
                'required',
                Rule::exists('email_service_providers', 'id')
                    ->where(function ($query) use ($group_id) {
                        $query->where('group_id', $group_id);
                    }),
            ],
            'template_id' => 'required|integer',
            'emails_per_lead' => 'required|integer',
            'days_between_emails' => 'nullable|integer',
        ];
    }
}
