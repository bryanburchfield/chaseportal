<?php

namespace App\Http\Requests;

use App\Models\ContactsPlaybook;
use App\Models\PlaybookTouch;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ValidPlaybookTouch extends FormRequest
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
        // otherwise, check that it belongs to user's group_id, 404 if not
        if ($this->filled('id')) {
            $playbook_touch = PlaybookTouch::where('id', $this->id)
                ->with('contacts_playbook')
                ->firstOrFail();
            if ($playbook_touch->contacts_playbook->group_id != Auth::user()->group_id) {
                abort(404);
            }
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
            'contacts_playbook_id' => [
                'required',
                Rule::exists('contacts_playbooks', 'id')
                    ->where(function ($query) use ($group_id) {
                        $query->where('group_id', $group_id);
                    }),
            ],
            'name' => [
                'required',
                Rule::unique('playbook_touch')->where(function ($query) {
                    return $query
                        ->where('id', '!=', $this->id)
                        ->where('contacts_playbook_id', $this->contacts_playbook_id)
                        ->whereNull('deleted_at');
                }),
            ],
            'campaign' => 'required',
            'subcampaign' => 'nullable',
        ];
    }
}
