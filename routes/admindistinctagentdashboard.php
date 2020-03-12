<?php
// Trend Dashboard: all urls start with /admindistinctagentdashboard/
Route::prefix('admindistinctagentdashboard')->group(function () {
    Route::group(['middleware' => 'can:accessSuperAdmin'], function () {
        // // Allow app_token login via /admindistinctagentdashboard/api/{token}
        // Route::get('/', 'AdminDistinctAgentDashController@apiLogin');
        // Route::get('api/{token}', 'AdminDistinctAgentDashController@apiLogin');
        Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

        // must be logged in to access any of these
        Route::group(['middleware' => 'auth'], function () {
            // ajax targets
            Route::post('update_filters', 'AdminDistinctAgentDashController@updateFilters');
            Route::post('call_volume', 'AdminDistinctAgentDashController@callVolume');
            Route::post('get_login_details', 'AdminDistinctAgentDashController@getLoginDetails');
        });
    });
});
