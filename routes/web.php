<?php

// Anything in the /public/raw directory will get processed outside the framework
Route::redirect('/raw', '/raw');

// Redirect root to /dashboards
Route::redirect('/', '/dashboards');

// This is for user logins
Auth::routes(['register' => false]);
Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');
