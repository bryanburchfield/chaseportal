<?php

namespace App\Listeners;

use App\Models\UserAudit;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    /**
     * Handle the event.
     *
     * @param  Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        UserAudit::create([
            'user_id' => $event->user->id,
            'email' => $event->user->email,
            'action' => 'Login',
        ]);
    }
}
