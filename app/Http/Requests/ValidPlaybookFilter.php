<?php

namespace App\Http\Requests;

use App\Models\PlaybookFilter;
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
        if ($this->has('id')) {
            $playbook_filter = PlaybookFilter::where('id', $this->id)
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
        $id = $this->id;

        return [
            'name' => [
                'required',
                Rule::unique('playbook_filters')->where(function ($query) use ($group_id, $id) {
                    return $query
                        ->where('group_id', $group_id)
                        ->where('id', '!=', $id);
                }),
            ],
            'campaign' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (!in_array($value, array_values($this->getAllCampaigns()))) {
                        $fail(trans('custom_validation.campaign_invalid'));
                    }
                },
            ],
            'field' => 'required',
            'operator' => 'required',
            'value' => new FilterValue($this->operator),
        ];
    }
}
