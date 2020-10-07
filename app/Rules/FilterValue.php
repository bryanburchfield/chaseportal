<?php

namespace App\Rules;

use App\Models\PlaybookFilter;
use Illuminate\Contracts\Validation\Rule;

class FilterValue implements Rule
{
    protected $operator;
    protected $error_message;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($operator)
    {
        $this->operator = $operator;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // If no operater selected, don't bother
        if (empty($this->operator)) {
            return true;
        }

        $detail = PlaybookFilter::operatorDetail($this->operator);

        if (empty($value)) {
            if ($detail['allow_nulls']) {
                return true;
            }
            $this->error_message = 'validation.required';
            return false;
        }

        if ($detail['value_type'] == 'date') {
            if ((strtotime($value)) === false) {
                $this->error_message = 'validation.date';
                return false;
            }
        }

        if ($detail['value_type'] == 'integer') {
            if ((!is_numeric($value))) {
                $this->error_message = 'validation.integer';
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
        return trans($this->error_message);
    }
}
