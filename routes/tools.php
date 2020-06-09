<?php

// Playbook Optout (outside of auth)
Route::get('/tools/playbook/optout', 'PlaybookController@optOut')->name('playbook.optout')->middleware('signed');

// Tools: all urls start with /tools/
Route::group(['middleware' => 'can:accessAdmin'], function () {
    Route::prefix('tools')->group(function () {
        Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

        // must be logged in to access any of these
        Route::group(['middleware' => 'auth'], function () {
            Route::redirect('/', 'tools/playbook');

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

            // Playbook
            Route::prefix('playbook')->group(function () {

                // Playbooks
                Route::get('/', 'PlaybookController@index');  // playbooks index
                Route::post('/playbooks', 'PlaybookController@addPlaybook');  // add a playbook
                Route::get('/playbooks/{id}', 'PlaybookController@getPlaybook');  // get a playbook by id
                Route::patch('/playbooks/{id}', 'PlaybookController@updatePlaybook');  // update a playbook by id
                Route::delete('/playbooks/{id}', 'PlaybookController@deletePlaybook');  // delete a playbook by id

                // Touches
                Route::get('/touches/{contacts_playbook_id}', 'PlaybookTouchController@index');  // touches index
                Route::get('/add_touch/{contacts_playbook_id}', 'PlaybookTouchController@addPlaybookTouchForm');  // add touch form
                Route::post('/touches/{contacts_playbook_id}', 'PlaybookTouchController@addPlaybookTouch');  // add a touch
                Route::get('/touches/touch/{id}', 'PlaybookTouchController@getPlaybookTouch');  // get a touch by id
                Route::patch('/touches/touch/{id}', 'PlaybookTouchController@updatePlaybookTouch');  // update a touch by id
                Route::delete('/touches/touch/{id}', 'PlaybookTouchController@deletePlaybookTouch');  // delete a touch by id

                // Playbook filters
                Route::get('/playbooks/filters/{id}', 'PlaybookController@getPlaybookFilters');  // get filters on a playbook by id
                Route::patch('/playbooks/filters/{id}', 'PlaybookController@saveFilters');  // add/update filters on a playbook by id

                // Playbook actions
                Route::get('/playbook/actions/{id}', 'PlaybookController@getPlaybookActions');  // get actions on a playbook by id
                Route::patch('/playbooks/actions/{id}', 'PlaybookController@saveActions');  // add/update filters on a playbook by id

                // Filters
                Route::get('/filters', 'PlaybookFilterController@index');  // filters index
                Route::post('/filters', 'PlaybookFilterController@addFilter');  // add a filter
                Route::get('/filters/{id}', 'PlaybookFilterController@getFilter');  // get a filter by id
                Route::patch('/filters/{id}', 'PlaybookFilterController@updateFilter');  // update a filter by id
                Route::delete('/filters/{id}', 'PlaybookFilterController@deleteFilter');  // delete a filter by id

                // Actions
                Route::get('/actions', 'PlaybookActionController@index');  // actions index
                Route::post('/actions', 'PlaybookActionController@addAction');  // add an action
                Route::get('/actions/{id}', 'PlaybookActionController@getAction');  // get an action by id
                Route::patch('/actions/{id}', 'PlaybookActionController@updateAction');  // update an action by id
                Route::delete('/actions/{id}', 'PlaybookActionController@deleteAction');  // delete an action by id

                // Email Serivce Providers
                Route::get('/email_service_providers', 'PlaybookEmailProviderController@index');
                Route::post('/get_provider_properties', 'PlaybookEmailProviderController@getProviderProperties');
                Route::post('/test_connection', 'PlaybookEmailProviderController@testConnection');
                Route::post('/get_esp', 'PlaybookEmailProviderController@getEmailServiceProvider');
                Route::post('/add_esp', 'PlaybookEmailProviderController@addEmailServiceProvider');
                Route::post('/delete_esp', 'PlaybookEmailProviderController@deleteEmailServiceProvider');
                Route::post('/update_esp', 'PlaybookEmailProviderController@updateEmailServiceProvider');

                // Shared ajax
                Route::post('/get_filters', 'PlaybookController@getFilters');  // get all available filters: pass in 'campaign' (optional)
                Route::post('/get_actions', 'PlaybookController@getActions');  // get all available actions: pass in 'campaign' (optional)
                Route::post('/get_filter_fields', 'PlaybookFilterController@getFilterFields');  // get all available fields: pass in 'campaign' (optional)
                Route::post('/get_operators', 'PlaybookFilterController@getOperators');  // get all available operators: pass in 'type' (optional)
                Route::post('/get_dispos', 'PlaybookActionController@getDispos');  // get all available dispos (call statuses): pass in 'campaign' (optional)
                Route::post('/get_subcampaigns', 'PlaybookActionController@getSubcampaigns'); // get all subcampaigns: pass in 'campaign' (required)
                Route::post('/get_table_fields', 'PlaybookActionController@getTableFields');  // get all custom table fields: pass in 'campaign' (required)
                Route::post('/toggle_playbook/', 'PlaybookController@toggleActive');  // toggles a playbook active/inactive: pass in 'id' (required)
                Route::post('/toggle_playbook_touch', 'PlaybookTouchController@toggleActive');  // toggles a touch active/inactive: pass in 'id' (required)
                Route::post('/activate_all_playbooks', 'PlaybookController@activateAllPlaybooks');  // toggles all playbooks active: pass in 'id' (required)
                Route::post('/deactivate_all_playbooks', 'PlaybookController@deactivateAllPlaybooks');  // toggles all playbooks inactive/inactive: pass in 'id' (required)

                // Superadmins can edit SMS from numbers
                Route::group(['middleware' => 'can:accessSuperAdmin'], function () {
                    Route::get('sms_numbers', 'SmsFromNumberController@index');
                    Route::post('/sms_number', 'SmsFromNumberController@store');
                    Route::patch('/sms_number/{id}', 'SmsFromNumberController@update');
                    Route::delete('/sms_number/{id}', 'SmsFromNumberController@destroy');
                    Route::get('/sms_number/{id}', 'SmsFromNumberController@getPlaybookSmsNumber');
                });
            });
        });
    });
});
