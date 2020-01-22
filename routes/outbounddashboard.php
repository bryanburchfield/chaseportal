<?php
// Admin Outbound Dashboard: all urls start with /outbounddashboard/
Route::prefix('outbounddashboard')->group(function () {
    // Allow app_token login via /outbounddashboard/api/{token}
    Route::get('/', 'OutboundDashController@apiLogin');
    Route::get('api/{token}', 'OutboundDashController@apiLogin');
    Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

    // must be logged in to access any of these
    Route::group(['middleware' => 'auth'], function () {

        // ajax targets
        Route::post('update_filters', 'OutboundDashController@updateFilters');
        Route::post('call_volume', 'OutboundDashController@callVolume');
        Route::post('completed_calls', 'OutboundDashController@completedCalls');
        Route::post('avg_hold_time', 'OutboundDashController@avgHoldTime');
        Route::post('avg_wait_time', 'OutboundDashController@avgWaitTime');
        Route::post('abandon_rate', 'OutboundDashController@abandonRate');
        Route::post('agent_call_count', 'OutboundDashController@agentCallCount');
        Route::post('service_level', 'OutboundDashController@serviceLevel');
        Route::post('rep_avg_handletime', 'OutboundDashController@repAvgHandleTime');
        Route::post('agent_talk_time', 'OutboundDashController@agentTalkTime');
        Route::post('sales_per_hour_per_rep', 'OutboundDashController@salesPerHourPerRep');
        Route::post('calls_by_campaign', 'OutboundDashController@callsByCampaign');
        Route::post('total_calls', 'OutboundDashController@totalCalls');
        Route::post('agent_call_status', 'OutboundDashController@agentCallStatus');
    });
});
