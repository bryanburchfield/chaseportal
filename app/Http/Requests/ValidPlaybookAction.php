<?php

namespace App\Http\Requests;

use App\Models\PlaybookAction;
use App\Models\PlaybookTouchAction;
use App\Traits\CampaignTraits;
use App\Traits\SqlServerTraits;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ValidPlaybookAction extends FormRequest
{
    use CampaignTraits;
    use SqlServerTraits;

    private $playbook_action;

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
        if (empty($this->playbook_action)) {
            $this->playbook_action = new PlaybookAction();
        } else {
            if ($this->playbook_action->group_id !== Auth::user()->group_id) {
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
                Rule::unique('playbook_actions')->where(function ($query) {
                    return $query
                        ->where('group_id', Auth::user()->group_id)
                        ->where('id', '!=', $this->playbook_action->id)
                        ->whereNull('deleted_at');
                }),
            ],
            'campaign' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (!in_array($value, array_values($this->getAllCampaigns()))) {
                        $fail(trans('custom_validation.campaign_invalid'));
                    }
                    // Don't allow update if action in use
                    if (
                        $this->playbook_action->id > 0 &&
                        $value !== $this->playbook_action->campaign
                    ) {
                        if (PlaybookTouchAction::where('playbook_action_id', $this->playbook_action->id)->first()) {
                            $fail(trans('custom_validation.action_cant_update_campaign'));
                        }
                    }
                },
            ],
            'action_type' => [
                'required',
                Rule::in(['email', 'sms', 'lead']),
            ],
        ];
    }
}
