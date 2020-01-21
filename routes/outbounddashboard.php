<?php
// Admin Outbound Dashboard: all urls start with /outbounddashboard/
Route::prefix('outbounddashboard')->group(function () {
    // Allow app_token login via /outbounddashboard/api/{token}
    Route::get('/', 'outboundDashController@apiLogin');
    Route::get('api/{token}', 'outboundDashController@apiLogin');
    Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

    // must be logged in to access any of these
    Route::group(['middleware' => 'auth'], function () {

        // ajax targets
        Route::post('update_filters', 'outboundDashController@updateFilters');
        Route::post('call_volume', 'outboundDashController@callVolume');
        Route::post('completed_calls', 'outboundDashController@completedCalls');
        Route::post('avg_hold_time', 'outboundDashController@avgHoldTime');
        Route::post('avg_wait_time', 'outboundDashController@avgWaitTime');
        Route::post('abandon_rate', 'outboundDashController@abandonRate');
        Route::post('agent_call_count', 'outboundDashController@agentCallCount');
        Route::post('service_level', 'outboundDashController@serviceLevel');
        Route::post('rep_avg_handletime', 'outboundDashController@repAvgHandleTime');
        Route::post('agent_talk_time', 'outboundDashController@agentTalkTime');
        Route::post('sales_per_hour_per_rep', 'outboundDashController@salesPerHourPerRep');
        Route::post('calls_by_campaign', 'outboundDashController@callsByCampaign');
        Route::post('total_calls', 'outboundDashController@totalCalls');
        Route::post('agent_call_status', 'outboundDashController@agentCallStatus');
    });
});
