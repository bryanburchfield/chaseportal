<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidRuleFilters implements Rule
{
    protected $filter_types = [
        'lead_age',
        'lead_attempts',
        'days_called',
    ];

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $filters = (array) $value;

        // make sure we have some filters
        if (count($filters) == 0) {
            return false;
        }

        foreach ($filters as $type => $val) {
            if (!in_array($type, $this->filter_types)) {
                return false;
            }
            if (is_numeric($val)) {
                $val = (int) $val;
            } else {
                $val = null;
            }
            if (!is_int($val) || $val <= 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('tools.invalid_filter');
    }
}
