<?php

namespace App\Http\Requests;

use App\Models\SmsFromNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ValidSmsFromNumber extends FormRequest
{
    protected $sms_from_number;

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
        if (empty($this->id)) {
            $this->sms_from_number = new SmsFromNumber($this->all());
        } else {
            $this->sms_from_number = SmsFromNumber::findOrFail($this->id);
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
                Rule::unique('sms_from_numbers')->where(function ($query) {
                    return $query->where('group_id', $this->sms_from_number->group_id)
                        ->where('from_number', $this->sms_from_number->from_number)
                        ->where('id', '!=', $this->sms_from_number->id);
                }),
            ],
        ];
    }
}
