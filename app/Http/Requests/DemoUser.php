<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Rules\UniqueEmail;
use App\Rules\UniqueName;
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

        // expiration only required on add
        return [
            'name' => [
                'required',
                new UniqueName($user),
            ],
            'email' => [
                'nullable',
                new UniqueEmail($user),
            ],
            'phone' => 'required',
            'expiration' => [
                Rule::requiredIf(empty($this->id)),
                'nullable',
                'integer',
            ],
        ];
    }
}
