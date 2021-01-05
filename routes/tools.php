<?php
// Tools: all urls start with /tools/
Route::group(['middleware' => 'can:accessAdmin'], function () {
    Route::prefix('tools')->group(function () {
        Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

        // must be logged in to access any of these
        Route::group(['middleware' => 'auth'], function () {

            Route::redirect('/', 'tools/contactflow_builder');
            Route::get('server_status', 'DialerController@index');

            // Contact Flow (leads)
            Route::prefix('contactflow_builder')->group(function () {
                Route::get('/', 'LeadsController@index');
                Route::get('/edit_rule/{id}', 'LeadsController@editLeadRule');
                Route::post('/', 'LeadsController@createRule');
                Route::post('/delete_rule', 'LeadsController@deleteRule');
                Route::post('/get_campaigns', 'LeadsController@getCampaigns');
                Route::post('/get_subcampaigns', 'LeadsController@getSubcampaigns');
                Route::post('/reverse_move', 'LeadsController@reverseMove');
                Route::post('/toggle_rule', 'LeadsController@toggleRule');
                Route::post('/update_rule', 'LeadsController@updateRule');
                Route::post('/view_rule', 'LeadsController@viewRule');
            });

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

            // Webook Generator
            Route::group(['middleware' => 'can:accessSuperAdmin'], function () {
                Route::get('webhook_generator', 'AdminController@webhookGenerator');
                Route::get('accounting_reports', 'AdminController@accountingReports');
                Route::get('form_builder', 'AdminController@formBuilder');
            });

            Route::redirect('/', action('DncController@index'));
        });
    });
});
