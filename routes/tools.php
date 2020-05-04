<?php
// Tools: all urls start with /tools/
Route::group(['middleware' => 'can:accessAdmin'], function () {
    Route::prefix('tools')->group(function () {
        Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

        // must be logged in to access any of these
        Route::group(['middleware' => 'auth'], function () {
            Route::redirect('/', 'tools/contactflow_builder');

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

            // Email Drip Builder
            Route::prefix('email_drip')->group(function () {
                Route::get('/', 'EmailDripController@index');
                Route::get('/update_filters/{email_drip_campaign_id}', 'EmailDripController@updateFilters');
                Route::post('/test_connection', 'EmailDripController@testConnection');
                Route::post('/add_esp', 'EmailDripController@addEmailServiceProvider');
                Route::post('/delete_esp', 'EmailDripController@deleteEmailServiceProvider');
                Route::post('/update_esp', 'EmailDripController@updateEmailServiceProvider');
                Route::post('/get_esp', 'EmailDripController@getEmailServiceProvider');
                Route::post('/add_campaign', 'EmailDripController@addEmailDripCampaign');
                Route::post('/delete_campaign', 'EmailDripController@deleteEmailDripCampaign');
                Route::post('/update_campaign', 'EmailDripController@updateEmailDripCampaign');
                Route::get('/edit_campaign/{id}', 'EmailDripController@editEmailDripCampaign');
                Route::post('/get_table_fields', 'EmailDripController@getTableFields');
                Route::post('/get_subcampaigns', 'EmailDripController@getSubcampaigns');
                Route::post('/get_properties', 'EmailDripController@getProperties');
                Route::post('/toggle_email_campaign', 'EmailDripController@toggleEmailDripCampaign');
                Route::post('/get_filters', 'EmailDripController@getFilters');
                Route::post('/get_operators', 'EmailDripController@getOperators');
                Route::post('/update_filters', 'EmailDripController@saveFilters');
                Route::post('/validate_filter', 'EmailDripController@validateFilter');
                Route::post('/delete_filter', 'EmailDripController@deleteFilter');
            });


            // Playbook
            Route::prefix('playbook')->group(function () {

                // Playbooks
                Route::get('/', 'PlaybookController@index');  // playbooks index
                Route::post('/playbooks', 'PlaybookController@addPlaybook');  // add a playbook
                Route::get('/playbooks/{id}', 'PlaybookController@getPlaybook');  // get a playbook by id
                Route::patch('/playbooks/{id}', 'PlaybookController@updatePlaybook');  // update a playbook by id
                Route::delete('/playbooks/{id}', 'PlaybookController@deletePlaybook');  // delete a playbook by id

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
                Route::post('/toggle_playbook', 'PlaybookController@toggleActive');  // toggles a playbook active/inactive: pass in 'id' (required)
                Route::post('/toggle_all_playbooks', 'PlaybookController@toggleAllActive');  // toggles a playbook active/inactive: pass in 'id' (required)
            });
        });
    });
});
