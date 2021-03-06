<?php

namespace App\Http\Requests;

use App\Models\PlaybookAction;
use App\Models\PlaybookTouch;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ValidPlaybookTouchAction extends FormRequest
{
    protected $playbook_touch;

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
        // check that playbook belongs to user's group_id, 404 if not
        $this->playbook_touch = PlaybookTouch::findOrFail($this->id);

        if (!$this->has('playbook_touch_actions')) {
            return;
        }

        // dedup first
        $actions = [];
        foreach ($this->playbook_touch_actions as $action) {
            $actions[$action] = $action;
        }
        $this->merge(['actions' => $actions]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'actions' => 'nullable|array',
        ];
    }

    public function withValidator($validator)
    {
        if (!$this->has('actions')) {
            return;
        }

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
