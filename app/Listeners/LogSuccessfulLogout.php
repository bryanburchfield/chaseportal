<?php

namespace App\Listeners;

use App\Models\UserAudit;
use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;

class LogSuccessfulLogout
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
     * @param  Logout  $event
     * @return void
     */
    public function handle(Logout $event)
    {
        // Check if this is from a cron job (set in controller)
        if (session('isCron', 0)) {
            return;
        }


        $action = 'Logout';

        // Special logout types
        if (session('isApi', 0)) {
            $action = 'API Logout';
        } elseif (session('isSso', 0)) {
            $action = 'SSO Logout';
        }

        UserAudit::create([
            'ip' => $this->request->ip(),
            'user_id' => $event->user->id,
            'email' => $event->user->email,
            'action' => $action,
        ]);
    }
}
