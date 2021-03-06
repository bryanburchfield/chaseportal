<?php
// KPIs: all urls start with /kpi/
Route::prefix('kpi')->group(function () {
    Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

    // must be logged in to access any of these
    Route::group(['middleware' => 'auth'], function () {

        // ajax targets
        Route::post('update_recipient', 'KpiController@updateRecipient');
        Route::post('run_kpi', 'KpiController@runKpi');
        Route::post('adjust_interval', 'KpiController@adjustInterval');
        Route::post('toggle_kpi', 'KpiController@toggleKpi');
        Route::post('remove_recipient_from_kpi', 'KpiController@removeRecipientFromKpi');
        Route::post('remove_recipient_from_all', 'KpiController@removeRecipient');
        Route::post('ajax_search', 'KpiController@searchRecipients');
        Route::post('get_recipient', 'KpiController@getRecipient');

        Route::group(['middleware' => 'can:accessSuperAdmin'], function () {
            Route::get('recipients/audit/{id}', 'KpiController@auditRecipient');
        });
    });
});
