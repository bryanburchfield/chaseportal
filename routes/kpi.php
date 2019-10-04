<?php
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
        Route::post('edit_recipient', 'KpiController@editRecipient');
        Route::post('update_recipient', 'KpiController@updateRecipient');
    });
});
