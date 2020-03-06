<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function updateSettings(Request $request)
    {
        // Add any missing settings (means they're unchecked)
        if ($request->missing('language_displayed')) {
            $request->merge(['language_displayed' => 0]);
        }
        if ($request->missing('theme')) {
            $request->merge(['theme' => 0]);
        }
        if ($request->missing('feature_message_notifications')) {
            $request->merge(['feature_message_notifications' => 0]);
        }

        // Theme = 1 means dark, otherwise light
        $theme = $request->theme ? 'dark' : 'light';

        // Update the user
        Auth()->User()->language_displayed = $request->language_displayed;
        Auth()->User()->feature_message_notifications = $request->feature_message_notifications;
        Auth()->User()->theme = $theme;
        Auth()->User()->save();

        return redirect()->back();
    }
}
