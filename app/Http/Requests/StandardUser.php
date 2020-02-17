<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;
use App\Rules\UniqueEmail;
use App\Rules\UniqueName;


class StandardUser extends FormRequest
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
        // Check if we're adding or editing
        if (!empty($this->id)) {
            $user = User::find($this->id);
        } else {
            $user = new User();
        }

        // expiration only required on add
        return [
            'group_id' => 'required|integer',
            'name' => [
                'required',
                new UniqueName($user),
            ],
            'email' => [
                'nullable',
                new UniqueEmail($user),
            ],
            'phone' => 'nullable',
            'tz' => 'required',
            'db' => 'required',
        ];
    }
}
