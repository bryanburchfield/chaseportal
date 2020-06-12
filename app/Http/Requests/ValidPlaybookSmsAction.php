<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
        $group_id = Auth::user()->group_id;

        return [
            'sms_from_number_id' => [
                'required',
                Rule::exists('sms_from_numbers', 'id')
                    ->where(function ($query) use ($group_id) {
                        $query->where('group_id', $group_id);
                    }),
            ],
            'template_id' => 'required|integer',
            'sms_per_lead' => 'required|integer',
            'days_between_sms' => 'nullable|integer',
        ];
    }
}
