<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
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

        if ($user && $user->password == md5($request->password)) {
            $user->password = Hash::make($request->password);
            $user->save();
        }

        // Continue as normal
        return $this->guard()->attempt(
            $this->credentials($request),
            $request->filled('remember')
        );
    }
}
