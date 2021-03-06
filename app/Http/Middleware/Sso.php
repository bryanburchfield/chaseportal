<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
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
        // Check if logged in and token changed, then log out
        if (Auth::check()) {
            if ($request->has('Token')) {
                if ($request->query('Token') !== session('ssoToken', '')) {
                    Auth::logout();
                    $request->session()->forget([
                        'isSso',
                        'ssoUsername',
                        'ssoToken',
                        'isSsoSuperadmin',
                    ]);
                }
            }
        }

        // if already logged in, don't bother
        if (Auth::guest()) {
            // Make sure token and server are passed
            if ($request->missing('Token') || $request->missing('Server')) {
                abort(403, 'Unauthorized');
            }

            // call API to get stuff
            $url = 'https://' . $request->query('Server') . '.chasedatacorp.com/Admin/SSO.aspx';

            // URL redirects, so we need cookies
            $jar = new CookieJar();
            $client = new Client();

            try {

                $response = $client->get(
                    $url,
                    [
                        'cookies' => $jar,
                        'query' => [
                            'Token' => $request->query('Token'),
                            'v' => 2,
                        ]
                    ]
                );
            } catch (Exception $e) {
                abort(403, 'Unauthorized');
            }

            try {
                $api_user = json_decode($response->getBody()->getContents());

                // Abort if not authorized
                if (
                    empty($api_user->Username) ||
                    empty($api_user->Role) ||
                    empty($api_user->GroupId) ||
                    empty($api_user->ReportingDatabase)
                ) {
                    abort(403, 'Unauthorized');
                }

                // Abort if group < -1
                $api_user->GroupId = (int) $api_user->GroupId;
                if ($api_user->GroupId < -1) {
                    abort(403, 'Unauthorized');
                }

                $sso_user = [
                    'name' => $api_user->Username,
                    'type' => $api_user->Role,
                    'group_id' => $api_user->GroupId,
                    'reporting_db' => $api_user->ReportingDatabase,
                    'timezone' => '',
                ];
            } catch (\Throwable $th) {
                abort(403, 'Unauthorized');
            }

            // Default TZ if superuser
            if ($api_user->GroupId == -1) {
                session(['isSsoSuperadmin' => 1]);
                $sso_user['timezone'] = 'Eastern Standard Time';
            }

            // Find or create the SSO user
            $user = User::getSsoUser($sso_user);

            // Abort if this blew up somehow
            if (!$user) {
                abort(403, 'Unauthorized');
            }

            // set 'sso' on session and save original name & token
            session([
                'isSso' => 1,
                'ssoUsername' => $api_user->Username,
                'ssoToken' => $request->query('Token'),
            ]);

            // Login as that user
            Auth::login($user);
        }

        return $next($request);
    }
}
