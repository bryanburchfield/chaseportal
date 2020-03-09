<?php

namespace App\Listeners;

use App\Models\LoginAudit;
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
        LoginAudit::create([
            'action' => 'Login',
            'email' => $event->user->email,
            'user_id' => $event->user->id,
        ]);
    }
}
