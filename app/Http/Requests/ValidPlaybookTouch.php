<?php

namespace App\Http\Requests;

use App\Models\ContactsPlaybook;
use App\Models\PlaybookAction;
use App\Models\PlaybookFilter;
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
        // We pass in a touch for update, or a playbook for add
        // check that it belongs to user's group_id, 403 if not
        if (!empty($this->playbook_touch)) {
            $this->contacts_playbook = $this->playbook_touch->contacts_playbook;
        } elseif (empty($this->contacts_playbook)) {
            abort(404);
        } else {
            $this->playbook_touch = new PlaybookTouch();
        }

        if ($this->contacts_playbook->group_id !== Auth::user()->group_id) {
            abort(403, 'Unauthorized');
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
                Rule::unique('playbook_touches')->where(function ($query) {
                    return $query
                        ->where('id', '!=', $this->playbook_touch->id)
                        ->where('contacts_playbook_id', $this->contacts_playbook->id)
                        ->whereNull('deleted_at');
                }),
            ],
            'actions' => 'required|array',
            'filters' => 'required|array',
        ];
    }

    public function withValidator($validator)
    {
        if ($this->has('filters')) {
            $validator->after(function ($validator) {
                // check that they're all valid
                foreach ($this->filters as $filter) {
                    $playbook_filter = PlaybookFilter::where('id', $filter)
                        ->where('group_id', Auth::user()->group_id)
                        ->first();

                    if (!$playbook_filter) {
                        $validator->errors()->add('filters', trans('custom_validation.filter_not_found'));
                    }
                }
            });
        }

        if ($this->has('actions')) {
            $validator->after(function ($validator) {
                // check that they're all valid
                foreach ($this->actions as $action) {
                    $playbook_action = PlaybookAction::where('id', $action)
                        ->where('group_id', Auth::user()->group_id)
                        ->first();

                    if (!$playbook_action) {
                        $validator->errors()->add('actions', trans('custom_validation.action_not_found'));
                    }
                }
            });
        }
    }
}
