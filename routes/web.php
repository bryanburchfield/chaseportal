<?php

// Dialer status page
Route::get('/dialer/status', 'DialerController@statusUrl');

// Language setter
Route::get('lang/{locale}', 'LocalizationController@lang');

// Redirect root to /dashboards
Route::redirect('/', '/dashboards');

// This is for user logins
Auth::routes(['register' => false]);
Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

// Allow app_token login via /demo/{token}
Route::get('demo/{token}', 'MasterDashController@demoLogin');
