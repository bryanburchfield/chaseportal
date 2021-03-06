<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
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
        $this->middleware('guest');
    }

    public function showResetForm(Request $request, $token = null)
    {
        $exists = false;

        $lifetime = config('auth.passwords.users.expire');

        $expires = Carbon::now()->subMinutes($lifetime)->toDateTimeString();

        $password_reset = DB::table('password_resets')
            ->whereEmail($request->email)
            ->where('created_at', '>', $expires)
            ->first();

        if ($password_reset) {
            // check if tokens match
            if (Hash::check($token, $password_reset->token)) {
                $exists = true;
            }
        }

        if ($exists) {
            return view('auth.passwords.reset')->with(
                ['token' => $token, 'email' => $request->email]
            );
        } else {
            return redirect('password/reset');
        }
    }
}
