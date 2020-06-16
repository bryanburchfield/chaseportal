<?php

namespace App\Http\Requests;

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
        // Playbook id comes from the url, it's not in $request->all()
        if (!empty($this->contacts_playbook_id)) {
            $this->merge(['contacts_playbook_id' => $this->contacts_playbook_id]);
        }

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
     * TODO:  'filters' and 'actions'
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
                Rule::unique('playbook_touches')->where(function ($query) {
                    return $query
                        ->where('id', '!=', $this->id)
                        ->where('contacts_playbook_id', $this->contacts_playbook_id)
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
