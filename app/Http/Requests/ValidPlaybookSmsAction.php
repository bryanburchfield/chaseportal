<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidPlaybookSmsAction extends FormRequest
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
        //we have to rename sms_script_id to template_idgroup_id, 404 if not
        if ($this->filled('sms_script_id')) {
            $this->merge(['template_id' => $this->sms_script_id]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'from_number' => 'required',
            'template_id' => 'required|integer',
            'sms_per_lead' => 'required|integer',
            'days_between_sms' => 'nullable|integer',
        ];
    }
}
