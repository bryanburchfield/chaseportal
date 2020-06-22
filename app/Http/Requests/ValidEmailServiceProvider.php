<?php

namespace App\Http\Requests;

use App\Models\EmailServiceProvider;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ValidEmailServiceProvider extends FormRequest
{

    private $email_service_provider;

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
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // if id not passed (adding), insert id=0
        // otherwise, check that belongs to user's group_id, 404 if not
        if ($this->filled('id')) {
            $this->email_service_provider = EmailServiceProvider::where('id', $this->id)
                ->where('group_id', Auth::user()->group_id)
                ->firstOrFail();
        } else {
            $this->email_service_provider = new EmailServiceProvider();
        }
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
            'name' => [
                'required',
                Rule::unique('email_service_providers')->where(function ($query) use ($group_id) {
                    return $query
                        ->where('group_id', $group_id)
                        ->where('id', '!=', $this->email_service_provider->id);
                }),
            ],
            'provider_type' => 'required',
        ];
    }
}
