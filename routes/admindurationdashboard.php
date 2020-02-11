<?php
// Trend Dashboard: all urls start with /admindurationdashboard/
Route::prefix('admindurationdashboard')->group(function () {
    // Allow app_token login via /admindurationdashboard/api/{token}
    Route::get('/', 'AdminDurationDashController@apiLogin');
    Route::get('api/{token}', 'AdminDurationDashController@apiLogin');
    Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

    // must be logged in to access any of these
    Route::group(['middleware' => 'auth'], function () {

        // ajax targets
        Route::post('update_filters', 'AdminDurationDashController@updateFilters');
    });
});
