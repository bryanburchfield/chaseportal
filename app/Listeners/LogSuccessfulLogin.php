<?php

namespace App\Listeners;

use App\Models\UserAudit;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;

class LogSuccessfulLogin
{
    private $request;

    /**
     * Create the event listener.
     *
     * @param  Request  $request
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle the event.
     *
     * @param  Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        // Check if this is from a cron job (set in controller)
        if (isset($event->user->cron)) {
            return;
        }

        UserAudit::create([
            'ip' => $this->request->ip(),
            'user_id' => $event->user->id,
            'email' => $event->user->email,
            'action' => 'Login',
        ]);
    }
}
