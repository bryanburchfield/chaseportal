<?php

namespace App\Listeners;

use App\Models\UserAudit;
use Illuminate\Auth\Events\Failed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogFailedLogin
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
     * @param  Failed  $event
     * @return void
     */
    public function handle(Failed $event)
    {
        $user_id = empty($event->user) ? null : $event->user->id;

        UserAudit::create([
            'ip' => $this->request->ip(),
            'user_id' => $user_id,
            'email' => $event->credentials['email'],
            'action' => 'Failed Login',
        ]);
    }
}
