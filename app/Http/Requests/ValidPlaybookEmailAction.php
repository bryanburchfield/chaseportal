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
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        //we have to rename email_script_id to template_idgroup_id, 404 if not
        if ($this->filled('email_script_id')) {
            $this->merge(['template_id' => $this->email_script_id]);
        }
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
            'subject' => 'required',
            'from' => 'required',
            'template_id' => 'required|integer',
            'email_field' => 'required',
            'email_service_provider_id' => [
                'required',
                Rule::exists('email_service_providers', 'id')
                    ->where(function ($query) use ($group_id) {
                        $query->where('group_id', $group_id);
                    }),
            ],
            'days_between_emails' => 'required|integer',
            'emails_per_lead' => 'required|integer',
        ];
    }
}
