<?php
// Compliance Dashboard: all urls start with /compliancedashboard/
Route::prefix('compliancedashboard')->group(function () {

    // must be logged in to access any of these
    Route::group(['middleware' => 'auth'], function () {
        Route::group(['middleware' => 'can:accessAdmin'], function () {
            Route::get('/settings', 'ComplianceDashController@settingsIndex');
            Route::post('/settings', 'ComplianceDashController@updateSettings');

            // ajax targets
            Route::post('get_compliance', 'ComplianceDashController@agentCompliance');
            Route::post('get_details/{rep}', 'ComplianceDashController@agentDetail');
        });
    });
});
