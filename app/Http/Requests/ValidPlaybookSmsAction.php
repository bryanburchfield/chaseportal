<?php

namespace App\Http\Requests;

use App\Models\PlaybookSmsNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $valid_from_numbers = PlaybookSmsNumber::whereIn('group_id', [0, Auth::user()->group_id])->get();

        return [
            'from_number' => [
                'required',
                function ($attribute, $value, $fail) use ($valid_from_numbers) {
                    if (!$valid_from_numbers->containsStrict('from_number', $value)) {
                        $fail(trans('custom_validation.invalid_sms_from_number'));
                    }
                },
            ],
            'template_id' => 'required|integer',
            'sms_per_lead' => 'required|integer',
            'days_between_sms' => 'nullable|integer',
        ];
    }
}
