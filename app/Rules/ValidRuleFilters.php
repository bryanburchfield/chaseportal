<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidRuleFilters implements Rule
{
    protected $filter_types = [
        'lead_age' => 'int',
        'lead_attempts' => 'int',
        'days_called' => 'int',
        'ring_group' => 'string',
        'call_status' => 'string',
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
        foreach ($value as $type => $val) {
            if (!in_array($type, array_keys($this->filter_types))) {
                return false;
            }
            if ($this->filter_types[$type] == 'int') {
                $val = is_numeric($val) ? (int) $val : null;
                if (!is_int($val) || $val < 0) {
                    return false;
                }
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
