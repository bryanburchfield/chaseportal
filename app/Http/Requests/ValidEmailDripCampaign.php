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
            'subcampaign' => 'nullable',
            'email_field' => 'required',
            'smtp_server_id' => [
                'required',
                Rule::exists('smtp_servers', 'id')
                    ->where(function ($query) use ($group_id) {
                        $query->where('group_id', $group_id);
                    }),
            ],
            'template_id' => 'required|integer',
        ];
    }
}
