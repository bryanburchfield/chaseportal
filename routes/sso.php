<?php
// SSO routes
Route::group(['prefix' => 'sso', 'middleware' => 'sso'], function () {
    Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');
    Route::post('/set_group', 'ReportController@setGroup');
    Route::post('/set_timezone', 'ReportController@setTiimezone');
    // reports
    Route::prefix('reports')->group(function () {
        Route::get('/{report}', 'ReportController@index');
        Route::post('/update_report', 'ReportController@updateReport');
        Route::get('/report_export/{report}/{format}', 'ReportController@exportReport');
    });
});
