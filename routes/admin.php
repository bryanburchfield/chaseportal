<?php
// Admin only
Route::prefix('admin')->group(function () {
    Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

    Route::group(['middleware' => 'can:accessAdmin'], function () {
        Route::post('add_user', 'AdminController@addUser');
        Route::post('delete_user', 'AdminController@deleteUser');
        Route::post('get_user', 'AdminController@getUser');
        Route::post('update_user', 'AdminController@updateUser');
        Route::post('get_client_tables', 'AdminController@getClientTables');
        Route::post('get_table_fields', 'AdminController@getTableFields');
        Route::get('manage_users', 'AdminController@manageUsers');
        Route::post('load_admin_nav', 'AdminController@loadAdminNav');
        Route::post('load_sidenav', 'AdminController@loadSideNav');
        Route::post('load_tools_nav', 'AdminController@loadToolsNav');
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
    });
});
