<?php

namespace App\Listeners;

use App\Models\LoginAudit;
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
        LoginAudit::create([
            'action' => 'Logout',
            'email' => $event->user->email,
            'user_id' => $event->user->id,
        ]);
    }
}
