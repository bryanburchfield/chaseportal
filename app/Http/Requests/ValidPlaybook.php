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
        // if model not passed (adding), insert new model
        // otherwise, check that it belongs to user's group_id, 403 if not
        if (empty($this->contacts_playbook)) {
            $this->contacts_playbook = new ContactsPlaybook();
        } else {
            if ($this->contacts_playbook->group_id !== Auth::user()->group_id) {
                abort(403, 'Unauthorized');
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => [
                'required',
                Rule::unique('contacts_playbooks')->where(function ($query) {
                    return $query
                        ->where('group_id', Auth::user()->group_id)
                        ->where('id', '!=', $this->contacts_playbook->id)
                        ->whereNull('deleted_at');
                }),
            ],
            'campaign' => 'required',
            'subcampaigns' => 'nullable|array',
        ];
    }
}
