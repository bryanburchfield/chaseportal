<?php

namespace App\Http\Requests;

use App\Recipient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EditRecipient extends FormRequest
{
    protected $errorBag = 'edit';

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
            'name' => [
                'required',
                Rule::unique('recipients')->where(function ($query) use ($recipient) {
                    return $query
                        ->where('group_id', $recipient->group_id)
                        ->where('id', '!=', $recipient->id)
                        ->whereNotNull('email');
                }),
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('recipients')->where(function ($query) use ($recipient) {
                    return $query
                        ->where('group_id', $recipient->group_id)
                        ->where('id', '!=', $recipient->id)
                        ->whereNotNull('email');
                }),
            ],
            'phone' => [
                'nullable',
                Rule::unique('recipients')->where(function ($query) use ($recipient) {
                    return $query
                        ->where('group_id', $recipient->group_id)
                        ->where('id', '!=', $recipient->id)
                        ->whereNotNull('phone');
                }),
            ],
        ];
    }
}
