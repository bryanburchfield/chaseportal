<?php
// Admin Dashboard: all urls start with /admininbounddashboard/
Route::prefix('admininbounddashboard')->group(function () {
    // Allow app_token login via /admininbounddashboard/api/{token}
    Route::get('/', 'AdminInboundDashController@apiLogin');
    Route::get('api/{token}', 'AdminInboundDashController@apiLogin');
    Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

    // must be logged in to access any of these
    Route::group(['middleware' => 'auth'], function () {

        // ajax targets
        Route::post('update_filters', 'AdminInboundDashController@updateFilters');
        Route::post('call_volume', 'AdminInboundDashController@callVolume');
        Route::post('total_sales', 'AdminInboundDashController@totalSales');
        Route::post('avg_hold_time', 'AdminInboundDashController@avgHoldTime');
        Route::post('abandon_rate', 'AdminInboundDashController@abandonRate');
        Route::post('agent_call_count', 'AdminInboundDashController@agentCallCount');
        Route::post('agent_call_status', 'AdminInboundDashController@agentCallStatus');
        Route::post('service_level', 'AdminInboundDashController@serviceLevel');
        Route::post('rep_avg_handletime', 'AdminInboundDashController@repAvgHandleTime');
        Route::post('agent_call_status', 'AdminInboundDashController@agentCallStatus');
    });
});
