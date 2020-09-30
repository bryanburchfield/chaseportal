<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DialerController extends Controller
{
    public function statusUrl()
    {
        return Auth::user()->dialer->status_url;
    }
}
