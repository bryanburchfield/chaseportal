<?php
// Agent Dashboard: all urls start with /agentdashboard/
Route::prefix('agentdashboard')->group(function () {
    Route::get('/', 'AgentCampaignDashController@apiLogin');
    Route::get('api/{token}/{rep}', 'AgentCampaignDashController@apiLogin');
    Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

    Route::group(['middleware' => 'auth'], function () {

        // ajax targets
        Route::post('update_filters', 'AgentCampaignDashController@agentUpdateFilters');
        Route::post('campaign_search', 'AgentCampaignDashController@agentCampaignSearch');
        Route::post('call_volume', 'AgentCampaignDashController@callVolume');
        Route::post('campaign_stats', 'AgentCampaignDashController@campaignStats');
        Route::post('campaign_chart', 'AgentCampaignDashController@campaignChart');
        Route::post('get_sales', 'AgentCampaignDashController@sales');
    });
});
