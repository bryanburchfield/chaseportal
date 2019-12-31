<?php
// Tools: all urls start with /tools/
Route::prefix('tools')->group(function () {
    Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

    // must be logged in to access any of these
    Route::group(['middleware' => 'auth'], function () {
        Route::redirect('/', 'tools/contactflow_builder');

        // Contact Flow (leads)
        Route::get('contactflow_builder/', 'LeadsController@index');
        Route::get('contactflow_builder/edit_rule/{id}', 'LeadsController@editLeadRule');
        Route::post('contactflow_builder/', 'LeadsController@createRule');
        Route::post('contactflow_builder/delete_rule', 'LeadsController@deleteRule');
        Route::post('contactflow_builder/get_campaigns', 'LeadsController@getCampaigns');
        Route::post('contactflow_builder/reverse_move', 'LeadsController@reverseMove');
        Route::post('contactflow_builder/toggle_rule', 'LeadsController@toggleRule');
        Route::post('contactflow_builder/update_rule', 'LeadsController@updateRule');
        Route::post('contactflow_builder/view_rule', 'LeadsController@viewRule');

        // DNC Import
        Route::get('dnc_importer/', 'DncController@index');
        Route::get('dnc_importer/upload', 'DncController@uploadIndex');
        Route::get('dnc_importer/errors/{id}', 'DncController@showErrors');
        Route::post('dnc_importer/', 'DncController@handleAction');
        Route::post('dnc_importer/delete_file', 'DncController@deleteFile');
        Route::post('dnc_importer/upload', 'DncController@uploadFile');
        Route::post('dnc_importer/process_file', 'DncController@processFile');
    });
});
