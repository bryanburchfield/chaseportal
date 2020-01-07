<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DncFile extends FormRequest
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
            'dncfile' => [
                'required',
                'mimes:txt,csv,xls,xlsx,ods,slk',
            ],
            'has_headers' => 'nullable',
            'description' => 'nullable',
        ];
    }
}
