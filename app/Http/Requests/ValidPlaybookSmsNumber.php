<?php

namespace App\Http\Requests;

use App\Models\PlaybookSmsNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ValidPlaybookSmsNumber extends FormRequest
{
    protected $playbook_sms_number;

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
        if ($this->filled('id')) {
            $this->playbook_sms_number = PlaybookSmsNumber::findOrFail($this->id);
        } else {
            $this->playbook_sms_number = new PlaybookSmsNumber($this->all());
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
            'group_id' => 'required|integer',
            'from_number' => [
                'required',
                'regex:/^\+1\d{10}$/',
                Rule::unique('playbook_sms_numbers')->where(function ($query) {
                    return $query->where('group_id', $this->playbook_sms_number->group_id)
                        ->where('from_number', $this->playbook_sms_number->from_number)
                        ->where('id', '!=', $this->playbook_sms_number->id);
                }),
            ],
        ];
    }
}
