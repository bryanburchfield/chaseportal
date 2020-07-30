<?php

namespace App\Http\Requests;

use App\Models\PlaybookFilter;
use App\Models\PlaybookTouchFilter;
use App\Rules\FilterValue;
use App\Traits\CampaignTraits;
use App\Traits\SqlServerTraits;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ValidPlaybookFilter extends FormRequest
{
    use CampaignTraits;
    use SqlServerTraits;

    private $playbook_filter;

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
        if (empty($this->playbook_filter)) {
            $this->playbook_filter = new PlaybookFilter();
        } else {
            if ($this->playbook_filter->group_id !== Auth::user()->group_id) {
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
                Rule::unique('playbook_filters')->where(function ($query) {
                    return $query
                        ->where('group_id', Auth::user()->group_id)
                        ->where('id', '!=', $this->playbook_filter->id);
                }),
            ],
            'campaign' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (!in_array($value, array_values($this->getAllCampaigns()))) {
                        $fail(trans('custom_validation.campaign_invalid'));
                    }
                    // Don't allow update if filter in use
                    if (
                        $this->playbook_filter->id > 0 &&
                        $value !== $this->playbook_filter->campaign
                    ) {
                        if (PlaybookTouchFilter::where('playbook_filter_id', $this->playbook_filter->id)->first()) {
                            $fail(trans('custom_validation.filter_cant_update_campaign'));
                        }
                    }
                },
            ],
            'field' => 'required',
            'operator' => 'required',
            'value' => new FilterValue($this->operator),
        ];
    }
}
