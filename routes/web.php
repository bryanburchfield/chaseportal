<?php

// Anything in the /public/raw directory will get processed outside the framework

Route::redirect('/raw', '/raw');

// Language setter
Route::get('lang/{locale}', 'LocalizationController@lang');

// Route to landing page
Route::get('/', 'MasterDashController@landingPage');

// This is for user logins
Auth::routes(['register' => false]);
Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

// Allow app_token login via /demo/{token}
Route::get('demo/{token}', 'MasterDashController@demoLogin');
