<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DemoUser extends FormRequest
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
        if (!empty($this->id)) {
            $user = User::find($this->id);
        } else {
            $user = new User();
        }

        return [
            'name' => [
                'required',
                Rule::unique('users')->ignore($user->id),
            ],
            'email' => [
                'nullable',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => 'required',
            'expiration' => 'required|integer',
        ];
    }
}
