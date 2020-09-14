<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Session;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        Session::flush();
        return redirect()->back();
    }

    /**
     * Attempt Login
     * Check for old md5 hashed pw and update in db if so
     *
     * @param Request $request
     * @return void
     */
    protected function attemptLogin(Request $request)
    {
        // see if the entered password matches md5 and update if so
        $user = User::where('email', $request->email)->first();

        $group = null;

        // update old md5 passwords to hash
        if ($user && $user->password == md5($request->password)) {
            $user->password = Hash::make($request->password);
            $user->save();
        }

        if ($user) {
            // set sqlsrv db and find group
            config(['database.connections.sqlsrv.database' => $user->db]);
            $group = Group::find($user->group_id);

            // mung password if group not active to force failed login
            if (!$group || $group->IsActive != 1) {
                $request->merge(['password' => 'youshallnotpass']);
            }
        }


        // Continue as normal
        return $this->guard()->attempt(
            $this->credentials($request),
            $request->filled('remember')
        );
    }

    protected function authenticated(Request $request, $user)
    {
        if (!empty($user->language)) {
            if (in_array($user->language, config('localization.locales'))) {
                session()->put('locale', $user->language);
            }
        }
    }
}
