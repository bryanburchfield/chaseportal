<?php
// Master dashboard: all urls start with /dashboards/
Route::prefix('dashboards')->group(function () {
    Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

    // ajax targets

    // must be logged in to access any of these
    Route::group(['middleware' => 'auth'], function () {
        Route::get('showreport', 'MasterDashController@showReport');
        Route::get('settings', 'MasterDashController@showSettings');
        Route::post('settings', 'MasterDashController@updateUserSettings');
        Route::get('/', 'MasterDashController@index')->middleware('check.password.changed');

        // Reports
        Route::get('automatedreports', 'AutomatedReportController@automatedReports');
        Route::post('toggle_automated_report', 'AutomatedReportController@toggleAutomatedReport');
        Route::post('reports/update_report', 'ReportController@updateReport');
        Route::post('reports/get_campaigns', 'ReportController@getCampaigns');
        Route::post('reports/get_subcampaigns', 'ReportController@getSubcampaigns');
        Route::get('reports/report_export/{report}/{format}', 'ReportController@exportReport');
        Route::get('reports/{report}', 'ReportController@index');
        Route::post('reports/{report}', 'ReportController@runReport');

        // ajax targets
        Route::post('set_dashboard', 'MasterDashController@setDashboard');
        Route::post('update_filters', 'MasterDashController@updateFilters');
        Route::post('campaign_search', 'MasterDashController@campaignSearch');

        // Admin only
        // prefix('admin') isn't working for some reason
        Route::group(['middleware' => 'can:accessAdmin'], function () {
            Route::get('admin/', 'Admin@index');
            Route::post('admin/add_user', 'Admin@addUser');
            Route::post('admin/delete_user', 'Admin@deleteUser');
            Route::post('admin/get_user', 'Admin@getUser');
            Route::post('admin/update_user', 'Admin@updateUser');
            Route::post('admin/cdr_lookup', 'Admin@cdrLookup');
        });
    });
});
