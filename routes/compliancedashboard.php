<?php
// Compliance Dashboard: all urls start with /compliancedashboard/
Route::prefix('compliancedashboard')->group(function () {

    // must be logged in to access any of these
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/settings', 'ComplianceDashController@settingsIndex');
        Route::post('/settings', 'ComplianceDashController@updateSettings');
    });
});
