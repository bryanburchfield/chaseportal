<?php

namespace App\Listeners;

use App\Models\UserAudit;
use Illuminate\Auth\Events\Logout;

class LogSuccessfulLogout
{
    /**
     * Handle the event.
     *
     * @param  Logout  $event
     * @return void
     */
    public function handle(Logout $event)
    {
        UserAudit::create([
            'user_id' => $event->user->id,
            'email' => $event->user->email,
            'action' => 'Logout',
        ]);
    }
}
