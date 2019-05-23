<?php

// Anything in the /public/raw directory will get processed outside the framework
Route::redirect('/raw', '/raw');

// Probably need a default landing page for this
Route::get('/', function () {
    phpinfo();
    // return 'Nothing to see here';
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
        Route::post('completed_calls', 'AdminDashController@completedCalls');
        Route::post('avg_hold_time', 'AdminDashController@avgHoldTime');
        Route::post('abandon_rate', 'AdminDashController@abandonRate');
        Route::post('agent_call_count', 'AdminDashController@agentCallCount');
        Route::post('service_level', 'AdminDashController@serviceLevel');
        Route::post('rep_avg_handletime', 'AdminDashController@repAvgHandleTime');
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
        Route::post('leader_board', 'LeaderDashController@leaderBoard');
        Route::post('call_volume', 'LeaderDashController@callVolume');
        Route::post('calls_by_campaign', 'LeaderDashController@callsByCampaign');
    });
});

// KPIs: all urls start with /kpi/
Route::prefix('kpi')->group(function () {
    Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');
    Route::post('optout', 'KpiController@optOut')->name('kpi.optout');
    //Route::post('optout', 'KpiController@optOut')->name('kpi.optout')->middleware('signed');

    // must be logged in to access any of these
    Route::group(['middleware' => 'auth'], function () {

        Route::get('/', 'KpiController@index');
        Route::get('recipients', 'KpiController@recipients');

        // ajax targets
        Route::post('run_kpi', 'KpiController@runKpi');
        Route::post('adjust_interval', 'KpiController@adjustInterval');
        Route::post('toggle_kpi', 'KpiController@toggleKpi');
        Route::post('add_recipient', 'KpiController@addRecipient');
        Route::post('remove_recipient', 'KpiController@removeRecipientFromKpi');
        Route::post('ajax_search', 'KpiController@searchRecipients');
    });
});

// Master dashboard: all urls start with /master/
Route::prefix('master')->group(function () {

    Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');
    
    // must be logged in to access any of these
    Route::group(['middleware' => 'auth'], function () {

        Route::get('/', 'MasterDashController@index');
        Route::get('recipients', 'MasterDashController@recipients');

        // Admin only
        Route::middleware('can:accessAdmin')->get('admin', 'MasterDashController@admin');

        // ajax targets
        Route::post('set_dashboard', 'MasterDashController@setDashboard');
        Route::post('update_report', 'MasterDashController@updateReport');
        Route::get('reports/{report}', 'ReportController@index');

        // Admin only
        Route::middleware('can:accessAdmin')->post('add_user', 'MasterDashController@addUser');
        Route::middleware('can:accessAdmin')->post('delete_user', 'MasterDashController@deleteUser');
    });
});
