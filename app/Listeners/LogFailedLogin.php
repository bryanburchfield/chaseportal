<?php

namespace App\Listeners;

use App\Models\UserAudit;
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

        UserAudit::create([
            'user_id' => $user_id,
            'email' => $event->credentials['email'],
            'action' => 'Failed',
        ]);
    }
}
