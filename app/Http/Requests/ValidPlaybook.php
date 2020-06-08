<?php

namespace App\Http\Requests;

use App\Models\ContactsPlaybook;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ValidPlaybook extends FormRequest
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
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // if id not passed (adding), insert id=0
        // otherwise, check that filter belongs to user's group_id, 404 if not
        if ($this->filled('id')) {
            $contacts_playbook = ContactsPlaybook::where('id', $this->id)
                ->where('group_id', Auth::user()->group_id)
                ->firstOrFail();
        } else {
            $this->merge(['id' => 0]);
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
                Rule::unique('contacts_playbooks')->where(function ($query) use ($group_id) {
                    return $query
                        ->where('group_id', $group_id)
                        ->where('id', '!=', $this->id);
                }),
            ],
            'campaign' => 'required',
            'subcampaign' => 'nullable',
        ];
    }
}
