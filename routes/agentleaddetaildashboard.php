<?php
// Agent Lead Detail Dashboard: all urls start with /agentleaddetaildashboard/
Route::prefix('agentleaddetaildashboard')->group(function () {
    Route::get('/', 'AgentLeadDetailDashController@apiLogin');
    Route::get('api/{token}/{rep}', 'AgentLeadDetailDashController@apiLogin');
    Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

    Route::group(['middleware' => 'auth'], function () {

        // ajax targets

    });
});
