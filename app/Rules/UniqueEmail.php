<?php

namespace App\Rules;

use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

class UniqueEmail implements Rule
{
    protected $user;
    protected $dup;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
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
        $this->dup = User::where('email', $value)
            ->where('id', '!=', $this->user->id)
            ->first();

        return ($this->dup) ? false : true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Email in use by ' . htmlspecialchars($this->dup->name) . ' on ' . $this->dup->dialer->reporting_db;
    }
}
