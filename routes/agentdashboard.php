<?php
// Agent Dashboard: all urls start with /agentdashboard/
Route::prefix('agentdashboard')->group(function () {
    Route::get('api/{token}/{rep}', 'AgentDashController@apiLogin');
    Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

    Route::group(['middleware' => 'auth'], function () {
        // There is no root route for this
        Route::get('/', function () {
            return redirect('agentdashboard/api/InvalidToken');
        });

        // ajax targets
        Route::post('call_volume', 'AgentDashController@callVolume');
        Route::post('rep_performance', 'AgentDashController@repPerformance');
        Route::post('call_status_count', 'AgentDashController@callStatusCount');
        Route::post('get_sales', 'AgentDashController@sales');
        Route::post('update_filters', 'AgentDashController@updateFilters');
    });
});
