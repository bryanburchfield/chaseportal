<?php
// Admin Dashboard: all urls start with /inbounddashboard/
Route::prefix('inbounddashboard')->group(function () {
    // Allow app_token login via /inbounddashboard/api/{token}
    Route::get('/', 'InboundDashController@apiLogin');
    Route::get('api/{token}', 'InboundDashController@apiLogin');
    Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

    // must be logged in to access any of these
    Route::group(['middleware' => 'auth'], function () {

        // ajax targets
        Route::post('update_filters', 'InboundDashController@updateFilters');
        Route::post('call_volume', 'InboundDashController@callVolume');
        Route::post('total_sales', 'InboundDashController@totalSales');
        Route::post('avg_hold_time', 'InboundDashController@avgHoldTime');
        Route::post('abandon_rate', 'InboundDashController@abandonRate');
        Route::post('agent_call_count', 'InboundDashController@agentCallCount');
        Route::post('agent_call_status', 'InboundDashController@agentCallStatus');
        Route::post('service_level', 'InboundDashController@serviceLevel');
        Route::post('rep_avg_handletime', 'InboundDashController@repAvgHandleTime');
        Route::post('agent_call_status', 'InboundDashController@agentCallStatus');
    });
});
