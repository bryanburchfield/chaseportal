<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use GuzzleHttp\Client;
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

            // Make sure token and server are passed
            if ($request->missing('Token') || $request->missing('Server')) {
                abort(403, 'Unauthorized');
            }

            // call API to get stuff
            $url = 'https://' . $request->query('Server') . '.chasedatacorp.com/Admin/SSO.aspx';
            // $url = 'https://' . $request->query('Server') . '.chasedatacorp.com/Admin/SSO.aspx?v=2&Token=' . $request->query('Token');

            $client = new Client();

            echo "<pre>";

            $response = $client->get(
                $url,
                [
                    'debug' => true,
                    'query' => [
                        'Token' => $request->query('Token'),
                        'v' => 2,
                    ]
                ]
            );
            $api_user = json_decode($response->getBody()->getContents());

            dd($api_user);

            if (true) {
                $sso_user = [
                    'name' => $api_user->Username,
                    'type' => $api_user->Role,
                    'group_id' => $api_user->GroupId,
                    'reporting_db' => $api_user->ReportingDatabase,
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
