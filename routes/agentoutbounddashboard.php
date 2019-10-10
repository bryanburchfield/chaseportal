<?php
// Agent Outbound Dashboard: all urls start with /agentoutbounddashboard/
Route::prefix('agentoutbounddashboard')->group(function () {
    Route::get('/', 'AgentOutboundDashController@apiLogin');
    Route::get('api/{token}/{rep}', 'AgentOutboundDashController@apiLogin');
    Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

    Route::group(['middleware' => 'auth'], function () {

        // ajax targets
        Route::post('call_status_count', 'AgentOutboundDashController@callStatusCount');
        Route::post('call_volume', 'AgentOutboundDashController@callVolume');
        Route::post('get_sales', 'AgentOutboundDashController@sales');
        Route::post('rep_performance', 'AgentOutboundDashController@repPerformance');
        Route::post('update_filters', 'AgentOutboundDashController@updateFilters');
    });
});
