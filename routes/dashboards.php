<?php
// Master dashboard: all urls start with /dashboards/
Route::prefix('dashboards')->group(function () {
    Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

    // ajax targets
    Route::post('reports/update_report', 'ReportController@updateReport');

    Route::get('reports/report_export/{report}/{format}', 'ReportController@exportReport');

    // must be logged in to access any of these
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/', 'MasterDashController@index');
        Route::post('showreport', 'MasterDashController@showReport');
        Route::get('settings/', 'MasterDashController@showSettings');

        // Reports
        Route::get('reportsettings', 'AutomatedReportController@reportSettings');
        Route::get('reports/{report}', 'ReportController@index');
        Route::post('reports/{report}', 'ReportController@runReport');
        Route::post('toggle_automated_report', 'AutomatedReportController@toggleAutomatedReport');
        Route::get('reports/get_subcampaigns', 'ReportController@getSubcampaigns');

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
