<?php
// Agent Outbound Dashboard: all urls start with /agentinbounddashboard/
Route::prefix('agentinbounddashboard')->group(function () {
    Route::get('/', 'AgentInboundDashController@apiLogin');
    Route::get('api/{token}/{rep}', 'AgentInboundDashController@apiLogin');
    Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

    Route::group(['middleware' => 'auth'], function () {

        // ajax targets
        Route::post('call_volume', 'AgentInboundDashController@callVolume');
        Route::post('rep_performance', 'AgentInboundDashController@repPerformance');
        Route::post('call_status_count', 'AgentInboundDashController@callStatusCount');
        Route::post('get_sales', 'AgentInboundDashController@sales');
        Route::post('update_filters', 'AgentInboundDashController@updateFilters');
    });
});
