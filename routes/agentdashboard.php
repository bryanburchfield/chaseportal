<?php
// Agent Dashboard: all urls start with /agentdashboard/
Route::prefix('agentdashboard')->group(function () {
    Route::get('/', 'AgentDashController@apiLogin');
    Route::get('api/{token}/{rep}', 'AgentDashController@apiLogin');
    Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

    Route::group(['middleware' => 'auth'], function () {

        // ajax targets
        Route::post('call_volume', 'AgentDashController@callVolume');
        Route::post('campaign_stats', 'AgentDashController@campaignStats');
        Route::post('call_status_count', 'AgentDashController@callStatusCount');
        Route::post('get_sales', 'AgentDashController@sales');
        Route::post('update_filters', 'AgentDashController@updateFilters');
    });
});
