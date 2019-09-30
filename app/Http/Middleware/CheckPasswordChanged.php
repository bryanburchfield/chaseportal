<?php

namespace App\Http\Middleware;

use Closure;

class CheckPasswordChanged
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
        if (empty($request->user()->password_changed_at)) {
            return redirect('/dashboards/settings');
        }

        return $next($request);
    }
}
