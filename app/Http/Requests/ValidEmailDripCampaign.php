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
        return [
            'name' => 'required',
            'description' => 'required',
            'campaign' => 'required',
            'subcampaign' => 'nullable',
            'email_field' => 'required',
            'smtp_server_id' => 'required',
            // Rule::exists('smtp_servers')->where(function ($query) {
            //     $query
            //         ->where('smtp_server_id', $this->smtp_server_id)
            //         ->where('group_id', Auth::User()->group_id);
            // }),
            'template_id' => 'required|integer',
        ];
    }
}
