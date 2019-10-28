<?php
// Leader Dashboard: all urls start with /leaderdashboard/
Route::prefix('leaderdashboard')->group(function () {
    // Allow app_token login via /Leaderdashboard/api/{token}
    Route::get('/', 'LeaderDashController@apiLogin');
    Route::get('api/{token}', 'LeaderDashController@apiLogin');
    Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

    // must be logged in to access any of these
    Route::group(['middleware' => 'auth'], function () {

        // ajax targets
        Route::post('update_filters', 'LeaderDashController@updateFilters');
        Route::post('call_volume', 'LeaderDashController@callVolume');
        Route::post('sales_per_campaign', 'LeaderDashController@salesPerCampaign');
        Route::post('call_details', 'LeaderDashController@callDetails');
    });
});
