<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Auth;

class Sso
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // if already logged in, don't bother
        if (Auth::guest()) {

            // call API to get stuff
            if ($request->query('token') == '12345') {
                $sso_user = [
                    'name' => 'bryan',
                    'type' => 'rep',
                    'group_id' => '777',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-07',
                    'timezone' => 'Eastern Standard Time',
                ];
            }

            // Abort if not authorized
            if (empty($sso_user['name'])) {
                abort(403, 'Unauthorized');
            }

            // Find or create the SSO user
            $user = User::getSsoUser($sso_user);

            // Abort if this blew up somehow
            if (!$user) {
                abort(403, 'Unauthorized');
            }

            // set 'sso' on session
            session(['isSso' => 1]);

            // Login as that user
            Auth::login($user);
        }

        return $next($request);
    }
}
