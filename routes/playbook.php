<?php

// Playbook Optout (outside of auth)
Route::get('/playbook/optout', 'PlaybookController@optOut')->name('playbook.optout')->middleware('signed');

// must be logged in to access any of these
Route::group(['middleware' => 'auth'], function () {
    Route::prefix('playbook')->group(function () {
        Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

        // Playbookss: all urls start with /playbook/
        Route::group(['middleware' => 'can:accessAdmin'], function () {

            // Playbooks
            Route::get('/', 'PlaybookController@index');  // playbooks index
            Route::post('/playbooks', 'PlaybookController@addPlaybook');  // add a playbook
            Route::get('/playbooks/{contacts_playbook}', 'PlaybookController@getPlaybook');  // get a playbook by id
            Route::patch('/playbooks/{contacts_playbook}', 'PlaybookController@updatePlaybook');  // update a playbook by id
            Route::delete('/playbooks/{contacts_playbook}', 'PlaybookController@deletePlaybook');  // delete a playbook by id

            // Touches
            Route::get('/touches/{contacts_playbook}', 'PlaybookTouchController@index');  // touches index
            Route::get('/add_touch/{contacts_playbook}', 'PlaybookTouchController@addPlaybookTouchForm');  // add touch form
            Route::get('/update_touch/{playbook_touch}', 'PlaybookTouchController@updatePlaybookTouchForm');  // update touch form
            Route::post('/touches/{contacts_playbook}', 'PlaybookTouchController@addPlaybookTouch');  // add a touch
            Route::get('/touches/touch/{playbook_touch}', 'PlaybookTouchController@getPlaybookTouch');  // get a touch by id
            Route::patch('/touches/touch/{playbook_touch}', 'PlaybookTouchController@updatePlaybookTouch');  // update a touch by id
            Route::delete('/touches/touch/{playbook_touch}', 'PlaybookTouchController@deletePlaybookTouch');  // delete a touch by id

            // Filters
            Route::get('/filters', 'PlaybookFilterController@index');  // filters index
            Route::post('/filters', 'PlaybookFilterController@addFilter');  // add a filter
            Route::get('/filters/{playbook_filter}', 'PlaybookFilterController@getFilter');  // get a filter by id
            Route::patch('/filters/{playbook_filter}', 'PlaybookFilterController@updateFilter');  // update a filter by id
            Route::delete('/filters/{playbook_filter}', 'PlaybookFilterController@deleteFilter');  // delete a filter by id

            // Actions
            Route::get('/actions', 'PlaybookActionController@index');  // actions index
            Route::post('/actions', 'PlaybookActionController@addAction');  // add an action
            Route::get('/actions/{playbook_action}', 'PlaybookActionController@getAction');  // get an action by id
            Route::patch('/actions/{playbook_action}', 'PlaybookActionController@updateAction');  // update an action by id
            Route::delete('/actions/{playbook_action}', 'PlaybookActionController@deleteAction');  // delete an action by id

            // Email Serivce Providers
            Route::get('/email_service_providers', 'PlaybookEmailProviderController@index');
            Route::post('/get_provider_properties', 'PlaybookEmailProviderController@getProviderProperties');
            Route::post('/test_connection', 'PlaybookEmailProviderController@testConnection');
            Route::post('/get_esp', 'PlaybookEmailProviderController@getEmailServiceProvider');
            Route::post('/add_esp', 'PlaybookEmailProviderController@addEmailServiceProvider');
            Route::post('/delete_esp', 'PlaybookEmailProviderController@deleteEmailServiceProvider');
            Route::post('/update_esp', 'PlaybookEmailProviderController@updateEmailServiceProvider');

            // History
            Route::prefix('history')->group(function () {
                Route::get('/', 'PlaybookHistoryController@index');  // history index
                Route::get('/run/{playbook_run}', 'PlaybookHistoryController@runIndex');  // run index
                Route::get('/run/action/{playbook_run_touch_action}', 'PlaybookHistoryController@runActionIndex');  // run action index
                Route::post('/reverse/action/{playbook_run_touch_action}', 'PlaybookHistoryController@reverseAction');  // reverse an action
            });

            // Shared ajax
            Route::post('/get_filters', 'PlaybookController@getFilters');  // get all available filters: pass in 'campaign' (optional)
            Route::post('/get_actions', 'PlaybookController@getActions');  // get all available actions: pass in 'campaign' (optional)
            Route::post('/get_filter_fields', 'PlaybookFilterController@getFilterFields');  // get all available fields: pass in 'campaign' (optional)
            Route::post('/get_operators', 'PlaybookFilterController@getOperators');  // get all available operators: pass in 'type' (optional)
            Route::post('/get_dispos', 'PlaybookActionController@getDispos');  // get all available dispos (call statuses): pass in 'campaign' (optional)
            Route::post('/get_subcampaigns', 'PlaybookActionController@getSubcampaigns'); // get all subcampaigns: pass in 'campaign' (required)
            Route::post('/get_table_fields', 'PlaybookActionController@getTableFields');  // get all custom table fields: pass in 'campaign' (required)
            Route::post('/toggle_playbook', 'PlaybookController@toggleActive');  // toggles a playbook active/inactive: pass in 'id' (required)
            Route::post('/toggle_playbook_touch', 'PlaybookTouchController@toggleActive');  // toggles a touch active/inactive: pass in 'id' (required)
            Route::post('/activate_all_playbooks', 'PlaybookController@activateAllPlaybooks');  // toggles all playbooks active: pass in 'id' (required)
            Route::post('/deactivate_all_playbooks', 'PlaybookController@deactivateAllPlaybooks');  // toggles all playbooks inactive/inactive: pass in 'id' (required)

            // Superadmins can edit SMS from numbers
            Route::group(['middleware' => 'can:accessSuperAdmin'], function () {
                Route::get('sms_numbers', 'SmsFromNumberController@index');
                Route::post('/sms_number', 'SmsFromNumberController@store');
                Route::patch('/sms_number/{sms_from_number}', 'SmsFromNumberController@update');
                Route::delete('/sms_number/{sms_from_number}', 'SmsFromNumberController@destroy');
                Route::get('/sms_number/{sms_from_number}', 'SmsFromNumberController@getSmsFromNumber');
            });
        });
    });
});
