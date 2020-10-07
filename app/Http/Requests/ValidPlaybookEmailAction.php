<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ValidPlaybookEmailAction extends FormRequest
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
            'subject' => 'required',
            'from' => 'required',
            'template_id' => 'required|integer',
            'email_field' => 'required',
            'email_service_provider_id' => [
                'required',
                Rule::exists('email_service_providers', 'id')
                    ->where(function ($query) {
                        $query->where('group_id', Auth::User()->group_id);
                    }),
            ],
            'days_between_emails' => 'required|integer',
            'emails_per_lead' => 'required|integer',
        ];
    }
}
