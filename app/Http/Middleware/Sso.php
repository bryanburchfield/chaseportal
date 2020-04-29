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



            // find or create 'fake' user
            // username will be user_group_a  or _r  for admin or rep
            // Case sensitive!!  Will probably have to create a hash for it

            if ($request->query('token') == '12345') {
                $user = User::find(38);
            } else {
                $user = User::find(-1);
            }

            // Abort if not authorized
            if (!$user) {
                abort(403, 'Unauthorized');
            }

            // Login as that user
            Auth::login($user);

            // set 'sso' on session
            session(['isSso' => 1]);
        }

        return $next($request);
    }
}
