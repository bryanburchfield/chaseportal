<?php
// Admin only
Route::prefix('admin')->group(function () {
    Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');
    Route::post('load_sidenav', 'AdminController@loadSidenav');

    Route::group(['middleware' => 'can:accessAdmin'], function () {
        Route::post('add_user', 'AdminController@addUser');
        Route::post('delete_user', 'AdminController@deleteUser');
        Route::post('get_user', 'AdminController@getUser');
        Route::post('update_user', 'AdminController@updateUser');
        Route::post('get_client_tables', 'AdminController@getClientTables');
        Route::post('get_table_fields', 'AdminController@getTableFields');
        Route::get('manage_users', 'AdminController@manageUsers');
        Route::post('toggle_user', 'AdminController@toggleUser');
    });

    Route::group(['middleware' => 'can:accessSuperAdmin'], function () {
        Route::post('add_demo_user', 'AdminController@addDemoUser');
        Route::post('update_demo_user', 'AdminController@updateDemoUser');
        Route::post('edit_myself', 'AdminController@editMyself');
        Route::get('settings', 'AdminController@settings');
        Route::get('notifications', 'FeatureMessageController@index');
        Route::get('notifications/{id}', 'FeatureMessageController@editMessage');
        Route::post('save_message', 'FeatureMessageController@saveMessage');
        Route::post('publish_notification', 'FeatureMessageController@publishMessage');
        Route::post('delete_msg', 'FeatureMessageController@deleteMsg');

        // Spam check
        Route::prefix('spam_check')->group(function () {
            Route::get('/', 'SpamCheckController@index');
            Route::get('/upload', 'SpamCheckController@uploadIndex');
            Route::get('/file/{id}', 'SpamCheckController@showRecords');
            Route::get('/errors/{id}', 'SpamCheckController@showErrors');
            Route::get('/flags/{id}', 'SpamCheckController@showFlags');
            Route::post('/single', 'SpamCheckController@submitNumber');
            Route::post('/upload', 'SpamCheckController@uploadFile');
            Route::post('/', 'SpamCheckController@handleAction');
        });
    });
});
