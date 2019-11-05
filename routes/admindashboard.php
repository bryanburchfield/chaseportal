<?php
// Admin Dashboard: all urls start with /admindashboard/
Route::prefix('admindashboard')->group(function () {
    // Allow app_token login via /admindashboard/api/{token}
    Route::get('/', 'AdminDashController@apiLogin');
    Route::get('api/{token}', 'AdminDashController@apiLogin');
    Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

    // must be logged in to access any of these
    Route::group(['middleware' => 'auth'], function () {

        // ajax targets
        Route::post('update_filters', 'AdminDashController@updateFilters');
        Route::post('call_volume', 'AdminDashController@callVolume');
        Route::post('total_sales', 'AdminDashController@totalSales');
        Route::post('avg_hold_time', 'AdminDashController@avgHoldTime');
        Route::post('abandon_rate', 'AdminDashController@abandonRate');
        Route::post('agent_call_count', 'AdminDashController@agentCallCount');
        Route::post('agent_call_status', 'AdminDashController@agentCallStatus');
        Route::post('service_level', 'AdminDashController@serviceLevel');
        Route::post('rep_avg_handletime', 'AdminDashController@repAvgHandleTime');
        Route::post('agent_call_status', 'AdminDashController@agentCallStatus');
    });
});
