<?php

namespace App\Http\Requests;

use App\Models\SmsFromNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
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
        // if model not passed (adding), insert new model
        if (empty($this->sms_from_number)) {
            $this->sms_from_number = new SmsFromNumber();
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
                    return $query
                        ->where('group_id', Auth::user()->group_id)
                        ->where('id', '!=', $this->sms_from_number->id);
                }),
            ],
        ];
    }
}
