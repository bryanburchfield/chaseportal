<?php
// Trend Dashboard: all urls start with /trenddashboard/
Route::prefix('trenddashboard')->group(function () {
    // Allow app_token login via /trenddashboard/api/{token}
    Route::get('/', 'TrendDashController@apiLogin');
    Route::get('api/{token}', 'TrendDashController@apiLogin');
    Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

    // must be logged in to access any of these
    Route::group(['middleware' => 'auth'], function () {

        // ajax targets
        Route::post('update_filters', 'TrendDashController@updateFilters');
        Route::post('call_volume', 'TrendDashController@callVolume');
        Route::post('call_details', 'TrendDashController@callDetails');
        Route::post('service_level', 'TrendDashController@serviceLevel');
        Route::post('agent_calltime', 'TrendDashController@agentCallTime');
    });
});
