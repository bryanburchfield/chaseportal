<?php

namespace App\Http\Middleware;

use Closure;
use App;

class Localization
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
        $locales = config('localization.locales');

        if (!session()->has('locale')) {
            session()->put('locale', $request->getPreferredLanguage($locales));
        }

        App::setLocale(session()->get('locale'));

        return $next($request);
    }
}
