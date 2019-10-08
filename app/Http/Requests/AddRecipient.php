<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AddRecipient extends FormRequest
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
            'kpi_list' => 'nullable',
            'name' => [
                'required',
                Rule::unique('recipients')->where(function ($query) use ($group_id) {
                    return $query
                        ->where('group_id', $group_id)
                        ->whereNotNull('email');
                }),
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('recipients')->where(function ($query) use ($group_id) {
                    return $query
                        ->where('group_id', $group_id)
                        ->whereNotNull('email');
                }),
            ],
            'phone' => [
                'nullable',
                Rule::unique('recipients')->where(function ($query) use ($group_id) {
                    return $query
                        ->where('group_id', $group_id)
                        ->whereNotNull('phone');
                }),
            ],
        ];
    }
}
