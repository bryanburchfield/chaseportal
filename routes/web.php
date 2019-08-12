<?php

// Anything in the /public/raw directory will get processed outside the framework
Route::redirect('/raw', '/raw');

// Probably need a default landing page for this
Route::get('/', function () {
    // phpinfo();
    return 'Nothing to see here';
});

// This is for user logins
Auth::routes(['register' => false]);
Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

// Admin Dashboard: all urls start with /admindashboard/
Route::prefix('admindashboard')->group(function () {
    // Allow app_token login via /admindashboard/api/{token}
    Route::get('api/{token}', 'AdminDashController@apiLogin');
    Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

    // must be logged in to access any of these
    Route::group(['middleware' => 'auth'], function () {

        Route::get('/', 'AdminDashController@index');

        // ajax targets
        Route::post('update_filters', 'AdminDashController@updateFilters');
        Route::post('call_volume', 'AdminDashController@callVolume');
        Route::post('total_sales', 'AdminDashController@totalSales');
        Route::post('avg_hold_time', 'AdminDashController@avgHoldTime');
        Route::post('abandon_rate', 'AdminDashController@abandonRate');
        Route::post('agent_call_count', 'AdminDashController@agentCallCount');
        Route::post('service_level', 'AdminDashController@serviceLevel');
        Route::post('rep_avg_handletime', 'AdminDashController@repAvgHandleTime');
        Route::post('average_hold_time', 'AdminDashController@avgHoldTime');
    });
});

// Admin Dashboard: all urls start with /adminoutbounddashboard/
Route::prefix('adminoutbounddashboard')->group(function () {
    // Allow app_token login via /adminoutbounddashboard/api/{token}
    Route::get('api/{token}', 'AdminOutboundDashController@apiLogin');
    Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

    // must be logged in to access any of these
    Route::group(['middleware' => 'auth'], function () {

        Route::get('/', 'AdminOutboundDashController@index');

        // ajax targets
        Route::post('update_filters', 'AdminOutboundDashController@updateFilters');
        Route::post('call_volume', 'AdminOutboundDashController@callVolume');
        Route::post('completed_calls', 'AdminOutboundDashController@completedCalls');
        Route::post('avg_hold_time', 'AdminOutboundDashController@avgHoldTime');
        Route::post('avg_wait_time', 'AdminOutboundDashController@avgWaitTime');
        Route::post('abandon_rate', 'AdminOutboundDashController@abandonRate');
        Route::post('agent_call_count', 'AdminOutboundDashController@agentCallCount');
        Route::post('service_level', 'AdminOutboundDashController@serviceLevel');
        Route::post('rep_avg_handletime', 'AdminOutboundDashController@repAvgHandleTime');
        Route::post('agent_talk_time', 'AdminOutboundDashController@agentTalkTime');
        Route::post('sales_per_hour_per_rep', 'AdminOutboundDashController@salesPerHourPerRep');
        Route::post('calls_by_campaign', 'AdminOutboundDashController@callsByCampaign');
        Route::post('total_calls', 'AdminOutboundDashController@totalCalls');
    });
});

// Trend Dashboard: all urls start with /trenddashboard/
Route::prefix('trenddashboard')->group(function () {
    // Allow app_token login via /trenddashboard/api/{token}
    Route::get('api/{token}', 'TrendDashController@apiLogin');
    Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

    // must be logged in to access any of these
    Route::group(['middleware' => 'auth'], function () {

        Route::get('/', 'TrendDashController@index');

        // ajax targets
        Route::post('update_filters', 'TrendDashController@updateFilters');
        Route::post('call_volume', 'TrendDashController@callVolume');
        Route::post('call_details', 'TrendDashController@callDetails');
        Route::post('service_level', 'TrendDashController@serviceLevel');
        Route::post('agent_calltime', 'TrendDashController@agentCallTime');
    });
});

// Leader Dashboard: all urls start with /leaderdashboard/
Route::prefix('leaderdashboard')->group(function () {
    // Allow app_token login via /Leaderdashboard/api/{token}
    Route::get('api/{token}', 'leaderDashController@apiLogin');
    Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

    // must be logged in to access any of these
    Route::group(['middleware' => 'auth'], function () {

        Route::get('/', 'LeaderDashController@index');

        // ajax targets
        Route::post('update_filters', 'LeaderDashController@updateFilters');
        // Route::post('leader_board', 'LeaderDashController@leaderBoard');
        Route::post('call_volume', 'LeaderDashController@callVolume');
        Route::post('sales_per_campaign', 'LeaderDashController@salesPerCampaign');
        // Route::post('sales_per_hour', 'LeaderDashController@salesPerHour');
        Route::post('call_details', 'LeaderDashController@callDetails');
    });
});

// KPIs: all urls start with /kpi/
Route::prefix('kpi')->group(function () {
    Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');
    // Route::post('optout', 'KpiController@optOut')->name('kpi.optout');
    Route::get('optout', 'KpiController@optOut')->name('kpi.optout')->middleware('signed');

    // must be logged in to access any of these
    Route::group(['middleware' => 'auth'], function () {

        Route::get('/', 'KpiController@index');
        Route::get('recipients', 'KpiController@recipients');

        // ajax targets
        Route::post('run_kpi', 'KpiController@runKpi');
        Route::post('adjust_interval', 'KpiController@adjustInterval');
        Route::post('toggle_kpi', 'KpiController@toggleKpi');
        Route::post('add_recipient', 'KpiController@addRecipient');
        Route::post('remove_recipient_from_kpi', 'KpiController@removeRecipientFromKpi');
        Route::post('remove_recipient_from_all', 'KpiController@removeRecipient');
        Route::post('ajax_search', 'KpiController@searchRecipients');
    });
});

// Master dashboard: all urls start with /dashboards/
Route::prefix('dashboards')->group(function () {

    Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

    // ajax targets
    Route::post('reports/update_report', 'ReportController@updateReport');

    // must be logged in to access any of these
    Route::group(['middleware' => 'auth'], function () {

        Route::get('/', 'MasterDashController@index');
        Route::post('showreport', 'MasterDashController@showReport');

        // Reports
        Route::get('reportsettings', 'AutomatedReportController@reportSettings');
        Route::get('reports/{report}', 'ReportController@index');
        Route::post('reports/{report}', 'ReportController@runReport');
        Route::post('toggle_automated_report', 'AutomatedReportController@toggleAutomatedReport');

        // ajax targets
        Route::post('set_dashboard', 'MasterDashController@setDashboard');
        Route::get('reports/get_subcampaigns', 'ReportController@getSubcampaigns');

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
