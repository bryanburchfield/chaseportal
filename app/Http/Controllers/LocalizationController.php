<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App;
use Illuminate\Support\Facades\Auth;

class LocalizationController extends Controller
{
    public function lang($locale)
    {
        if (in_array($locale, config('localization.locales'))) {
            App::setLocale($locale);
            session()->put('locale', $locale);

            if (Auth::check()) {
                $user = Auth::user();
                $user->language = $locale;
                $user->save();
            }
        }
        return redirect()->back();
    }
}
