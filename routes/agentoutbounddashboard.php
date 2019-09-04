<?php
// Agent Outbound Dashboard: all urls start with /agentoutbounddashboard/
Route::prefix('agentoutbounddashboard')->group(function () {
    Route::get('api/{token}/{rep}', 'AgentOutboundDashController@apiLogin');
    Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

    Route::group(['middleware' => 'auth'], function () {
        // There is no root route for this
        Route::get('/', function () {
            return redirect('agentoutbounddashboard/api/InvalidToken');
        });

        // ajax targets
        Route::post('call_status_count', 'AgentOutboundDashController@callStatusCount');
        Route::post('call_volume', 'AgentOutboundDashController@callVolume');
        Route::post('get_sales', 'AgentOutboundDashController@sales');
        Route::post('rep_performance', 'AgentOutboundDashController@repPerformance');
        Route::post('update_filters', 'AgentOutboundDashController@updateFilters');
    });
});
