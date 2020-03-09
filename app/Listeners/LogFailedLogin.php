<?php

namespace App\Listeners;

use App\Models\LoginAudit;
use Illuminate\Auth\Events\Failed;

class LogFailedLogin
{
    /**
     * Handle the event.
     *
     * @param  Failed  $event
     * @return void
     */
    public function handle(Failed $event)
    {
        $user_id = empty($event->user) ? null : $event->user->id;

        LoginAudit::create([
            'action' => 'Failed',
            'email' => $event->credentials['email'],
            'user_id' => $user_id,
        ]);
    }
}
