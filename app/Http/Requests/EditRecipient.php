<?php

namespace App\Http\Requests;

use App\Recipient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;

class EditRecipient extends FormRequest
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
        $recipient = Recipient::findOrFail($this->recipient_id);

        return [
            'recipient_id' => 'required',
            'kpi_list' => 'nullable',
            'edit_name' => [
                'required',
                Rule::unique('recipients', 'name')->where(function ($query) use ($recipient) {
                    return $query
                        ->where('group_id', $recipient->group_id)
                        ->where('id', '!=', $recipient->id)
                        ->whereNotNull('email');
                }),
            ],
            'edit_email' => [
                'required',
                'email',
                Rule::unique('recipients', 'email')->where(function ($query) use ($recipient) {
                    return $query
                        ->where('group_id', $recipient->group_id)
                        ->where('id', '!=', $recipient->id)
                        ->whereNotNull('email');
                }),
            ],
            'edit_phone' => [
                'nullable',
                Rule::unique('recipients', 'phone')->where(function ($query) use ($recipient) {
                    return $query
                        ->where('group_id', $recipient->group_id)
                        ->where('id', '!=', $recipient->id)
                        ->whereNotNull('phone');
                }),
            ],
        ];
    }

    public function attributes()
    {
        return [
            'edit_email' => 'email',
            'edit_name' => 'name',
            'edit_phone' => 'phone',
        ];
    }
}
