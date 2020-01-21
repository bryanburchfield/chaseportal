<?php
// Admin Dashboard: all urls start with /inbounddashboard/
Route::prefix('inbounddashboard')->group(function () {
    // Allow app_token login via /inbounddashboard/api/{token}
    Route::get('/', 'inboundDashController@apiLogin');
    Route::get('api/{token}', 'inboundDashController@apiLogin');
    Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

    // must be logged in to access any of these
    Route::group(['middleware' => 'auth'], function () {

        // ajax targets
        Route::post('update_filters', 'inboundDashController@updateFilters');
        Route::post('call_volume', 'inboundDashController@callVolume');
        Route::post('total_sales', 'inboundDashController@totalSales');
        Route::post('avg_hold_time', 'inboundDashController@avgHoldTime');
        Route::post('abandon_rate', 'inboundDashController@abandonRate');
        Route::post('agent_call_count', 'inboundDashController@agentCallCount');
        Route::post('agent_call_status', 'inboundDashController@agentCallStatus');
        Route::post('service_level', 'inboundDashController@serviceLevel');
        Route::post('rep_avg_handletime', 'inboundDashController@repAvgHandleTime');
        Route::post('agent_call_status', 'inboundDashController@agentCallStatus');
    });
});
