<?php

namespace App\Http\Requests;

use App\Models\ContactsPlaybook;
use App\Models\PlaybookFilter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ValidContactsPlaybookFilter extends FormRequest
{
    protected $contacts_playbook;

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
        $this->contacts_playbook = ContactsPlaybook::findOrFail($this->id);

        if (!$this->has('filters')) {
            return;
        }

        // dedup first
        $filters = [];
        foreach ($this->filters as $filter) {
            $filters[$filter] = $filter;
        }
        $this->merge(['filters' => $filters]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'filters' => 'nullable|array',
        ];
    }

    public function withValidator($validator)
    {
        if (!$this->has('filters')) {
            return;
        }

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
}
