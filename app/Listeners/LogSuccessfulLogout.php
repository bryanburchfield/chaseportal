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
        UserAudit::create([
            'ip' => $this->request->ip(),
            'user_id' => $event->user->id,
            'email' => $event->user->email,
            'action' => 'Logout',
        ]);
    }
}
