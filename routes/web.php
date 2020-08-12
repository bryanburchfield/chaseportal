<?php

// Anything in the /public/raw directory will get processed outside the framework
Route::redirect('/raw', '/raw');

// Language setter
Route::get('lang/{locale}', 'LocalizationController@lang');

// Redirect root to /dashboards
Route::redirect('/', '/dashboards');

// This is for user logins
Auth::routes(['register' => false]);
Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

// Allow app_token login via /demo/{token}
Route::get('demo/{token}', 'MasterDashController@demoLogin');

// Test for RT dashes
Route::get('/rt_test', 'RealTimeDashboardController@index');
