<?php
// Admin Outbound Dashboard: all urls start with /adminoutbounddashboard/
Route::prefix('adminoutbounddashboard')->group(function () {
    // Allow app_token login via /adminoutbounddashboard/api/{token}
    Route::get('api/{token}', 'AdminOutboundDashController@apiLogin');
    Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

    // must be logged in to access any of these
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/', 'AdminOutboundDashController@index');

        // ajax targets
        Route::post('update_filters', 'AdminOutboundDashController@updateFilters');
        Route::post('call_volume', 'AdminOutboundDashController@callVolume');
        Route::post('completed_calls', 'AdminOutboundDashController@completedCalls');
        Route::post('avg_hold_time', 'AdminOutboundDashController@avgHoldTime');
        Route::post('avg_wait_time', 'AdminOutboundDashController@avgWaitTime');
        Route::post('abandon_rate', 'AdminOutboundDashController@abandonRate');
        Route::post('agent_call_count', 'AdminOutboundDashController@agentCallCount');
        Route::post('service_level', 'AdminOutboundDashController@serviceLevel');
        Route::post('rep_avg_handletime', 'AdminOutboundDashController@repAvgHandleTime');
        Route::post('agent_talk_time', 'AdminOutboundDashController@agentTalkTime');
        Route::post('sales_per_hour_per_rep', 'AdminOutboundDashController@salesPerHourPerRep');
        Route::post('calls_by_campaign', 'AdminOutboundDashController@callsByCampaign');
        Route::post('total_calls', 'AdminOutboundDashController@totalCalls');
        Route::post('agent_call_status', 'AdminOutboundDashController@agentCallStatus');
    });
});
