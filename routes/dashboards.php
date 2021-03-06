<?php
// Master dashboard: all urls start with /dashboards/
Route::prefix('dashboards')->group(function () {
    Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

    // must be logged in to access any of these
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/inbounddashboard', 'MasterDashController@inboundDashboard');
        Route::get('/outbounddashboard', 'MasterDashController@outboundDashboard');
        Route::get('/leaderdashboard', 'MasterDashController@leaderDashboard');
        Route::get('/trenddashboard', 'MasterDashController@trendDashboard');
        Route::get('/realtimeagentdashboard', 'MasterDashController@realtimeAgentDashboard');

        Route::get('/kpi', 'MasterDashController@kpi');
        Route::get('/kpi/recipients', 'KpiController@recipients');
        Route::post('/kpi/recipients', 'KpiController@addRecipient');
        Route::get('/kpi/optout', 'KpiController@optOut')->name('kpi.optout')->middleware('signed');

        Route::get('showreport', 'MasterDashController@showReport');
        Route::get('settings', 'MasterDashController@showSettings');
        Route::post('settings', 'MasterDashController@updateUserSettings');
        Route::post('settings/update_settings', 'UserController@updateSettings');

        // Reports
        Route::get('automatedreports', 'AutomatedReportController@automatedReports');
        Route::post('toggle_automated_report', 'AutomatedReportController@toggleAutomatedReport');
        Route::post('reports/update_report', 'ReportController@updateReport');
        Route::post('reports/get_campaigns', 'ReportController@getCampaigns');
        Route::post('reports/get_subcampaigns', 'ReportController@getSubcampaigns');
        Route::get('reports/report_export/{report}/{format}', 'ReportController@exportReport');
        Route::get('reports/{report}', 'ReportController@index');
        Route::post('reports/{report}', 'ReportController@runReport');
        Route::get('reports/info/{report}', 'ReportController@info');

        // Notifications
        Route::get('/notifications/{id}', 'FeatureMessageController@viewMessage');
        Route::post('feature_msg_read', 'FeatureMessageController@readMessage');

        // ajax targets
        Route::post('set_dashboard', 'MasterDashController@setDashboard');
        Route::post('update_filters', 'MasterDashController@updateFilters');
        Route::post('campaign_search', 'MasterDashController@campaignSearch');
        Route::post('feature_msg_read', 'FeatureMessageController@readMessage');
        Route::get('get_lead_info/{lead}', 'RealTimeDashboardController@getLeadInfo');
        Route::post('admin/get_groups', 'AdminController@getGroups');

        // Admin only dashboards
        Route::group(['middleware' => 'can:accessAdmin'], function () {
            Route::get('/compliancedashboard', 'MasterDashController@complianceDashboard');
        });
        // Super Admin only dashboards
        Route::group(['middleware' => 'can:accessSuperAdmin'], function () {
            Route::get('/admindistinctagentdashboard', 'MasterDashController@adminDistinctAgentDashboard');
            Route::get('/admindurationdashboard', 'MasterDashController@adminDurationDashboard');
        });

        Route::redirect('/', action('MasterDashController@inboundDashboard'));
    });
});
