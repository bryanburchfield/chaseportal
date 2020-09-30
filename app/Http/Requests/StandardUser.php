<?php

namespace App\Http\Requests;

use App\Http\Controllers\AdminController;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;
use App\Rules\UniqueEmail;
use Illuminate\Support\Facades\Auth;

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
        $admincontroller = new AdminController;

        $valid_user_types = array_keys($admincontroller->userTypes());

        // Check if we're adding or editing
        if (!empty($this->id)) {
            $user = User::find($this->id);
        } else {
            $user = new User();
        }

        // expiration only required on add
        return [
            'group_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    if (!Auth::User()->isType('superadmin')) {
                        if ($value != Auth::User()->group_id) {
                            $fail(trans('custom_validation.group_must_be') . ' ' . Auth::User()->group_id);
                        }
                    }
                },
            ],
            'name' => 'required',
            'email' => [
                'nullable',
                new UniqueEmail($user),
            ],

            'user_type' => [
                'required',
                function ($attribute, $value, $fail) use ($valid_user_types) {
                    if (!in_array($value, $valid_user_types)) {
                        $fail(trans('custom_validation.invalid_user_type'));
                    }
                },
            ],
            'phone' => 'required',
            'tz' => 'required',
            'dialer_id' => 'required',
        ];
    }
}
