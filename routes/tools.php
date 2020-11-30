<?php
// Tools: all urls start with /tools/
Route::prefix('tools')->group(function () {
    Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

    // must be logged in to access any of these
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/lead_detail/{lead?}', 'LeadsController@leadDetail');
        Route::post('/get_lead', 'LeadsController@getLead');

        // must be logged in to access any of these
        Route::group(['middleware' => 'can:accessAdmin'], function () {
            Route::post('/update_lead/{lead}', 'LeadsController@updateLead');

            Route::get('server_status', 'DialerController@index');
            // DNC Import
            Route::prefix('dnc_importer')->group(function () {
                Route::get('/', 'DncController@index');
                Route::get('/upload', 'DncController@uploadIndex');
                Route::get('/errors/{id}', 'DncController@showErrors');
                Route::get('/file/{id}', 'DncController@showRecords');
                Route::post('/', 'DncController@handleAction');
                Route::post('/delete_file', 'DncController@deleteFile');
                Route::post('/upload', 'DncController@uploadFile');
                Route::post('/process_file', 'DncController@processFile');
            });

            // CDR Lookup
            Route::get('cdr_lookup', 'AdminController@loadCdrLookup');
            Route::post('cdr_lookup', 'AdminController@cdrLookup');

            // Webook Generator & Form Builder
            Route::group(['middleware' => 'can:accessSuperAdmin'], function () {
                Route::get('webhook_generator', 'AdminController@webhookGenerator');
                Route::get('form_builder', 'AdminController@formBuilder');
                Route::get('accounting_reports', 'AdminController@accountingReports');
            });

            Route::redirect('/', action('DncController@index'));
        });
    });
});
