<?php
// Master dashboard: all urls start with /dashboards/
Route::prefix('dashboards')->group(function () {
    Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

    // must be logged in to access any of these
    Route::group(['middleware' => 'auth'], function () {
        Route::redirect('/', 'dashboards/inbounddashboard');
        Route::get('/inbounddashboard', 'MasterDashController@inboundDashboard');
        Route::get('/outbounddashboard', 'MasterDashController@outboundDashboard');
        Route::get('/leaderdashboard', 'MasterDashController@leaderDashboard');
        Route::get('/trenddashboard', 'MasterDashController@trendDashboard');

        Route::get('/kpi', 'MasterDashController@kpi');
        Route::get('/kpi/recipients', 'KpiController@recipients');
        Route::post('/kpi/recipients', 'KpiController@addRecipient');
        Route::get('/kpi/optout', 'KpiController@optOut')->name('kpi.optout')->middleware('signed');

        Route::get('showreport', 'MasterDashController@showReport');
        Route::get('settings', 'MasterDashController@showSettings');
        Route::post('settings', 'MasterDashController@updateUserSettings');
        Route::post('settings/update_lang_display', 'MasterDashController@updateLangDisplay');
        Route::post('settings/update_theme', 'MasterDashController@updateTheme');

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
            // Route::get('admin/', 'AdminController@index');
            Route::post('admin/add_user', 'AdminController@addUser');
            Route::post('admin/add_demo_user', 'AdminController@addDemoUser');
            Route::post('admin/delete_user', 'AdminController@deleteUser');
            Route::post('admin/get_user', 'AdminController@getUser');
            Route::post('admin/update_user', 'AdminController@updateUser');
            Route::post('admin/update_demo_user', 'AdminController@updateDemoUser');
            Route::get('admin/cdr_lookup', 'AdminController@loadCdrLookup');
            Route::post('admin/cdr_lookup', 'AdminController@cdrLookup');
            Route::get('admin/webhook_generator', 'AdminController@webhookGenerator');
            Route::get('admin/settings', 'AdminController@settings');
            Route::post('admin/edit_myself', 'AdminController@editMyself');
            Route::post('admin/get_client_tables', 'AdminController@getClientTables');
            Route::post('admin/get_table_fields', 'AdminController@getTableFields');
            Route::get('admin/manage_clients', 'AdminController@manageClients');
            Route::get('admin/load_admin_nav', function(){
                return view('/shared.admin_sidenav');
            });
        });

        Route::get('load_sidenav', function(){
            return redirect()->back();
        });
    });
});
