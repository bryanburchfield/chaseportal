<?php

namespace App\Scripts;

use App\User;

// do a php export of the users table from the old db and paste here
$users = array(
    array('user_id' => '90', 'user_type' => 'client', 'group_id' => '432', 'name' => 'adsfad asdfadsf', 'email' => 'asdfasdsfadsd@asdfasdf.com', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99', 'db' => 'PowerV2_Reporting_Dialer-01', 'tz' => 'Azores Standard Time', 'created_pw' => '0', 'fpass_token' => NULL, 'token_created' => NULL, 'app_token' => '1da1599dc7b606b983c5b0318387df01', 'password_updated' => NULL, 'additional_dbs' => 'PowerV2_Reporting_Dialer-02', 'persist_filters' => NULL),
    array('user_id' => '91', 'user_type' => 'client', 'group_id' => '678', 'name' => 'last test', 'email' => 'pwoetpoe@wept.com', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99', 'db' => 'PowerV2_Reporting_Dialer-01', 'tz' => 'Azores Standard Time', 'created_pw' => '0', 'fpass_token' => NULL, 'token_created' => NULL, 'app_token' => '47e10711c3b29d38565c125b5768b6ed', 'password_updated' => NULL, 'additional_dbs' => '', 'persist_filters' => NULL),
    array('user_id' => '1', 'user_type' => 'admin', 'group_id' => '777', 'name' => 'Test User', 'email' => 'test@chase.com', 'password' => 'e0bb6103bc5c832a2c6b520b16db51ce', 'db' => 'PowerV2_Reporting_Dialer-17', 'tz' => 'Eastern Standard Time', 'created_pw' => '1', 'fpass_token' => 'g7QgNGwgx1pZ6iYooCRS0yoOC0jFPl', 'token_created' => '2019-03-19 16:34:51', 'app_token' => 'F1C1592588411002AF340CBAEDD6FC33', 'password_updated' => '2019-03-19 16:35:14', 'additional_dbs' => '', 'persist_filters' => '{"campaign":[]}'),
    array('user_id' => '92', 'user_type' => 'client', 'group_id' => '777', 'name' => 'Thomas Grauer', 'email' => 'tgrauer1@me.com', 'password' => '098f6bcd4621d373cade4e832627b4f6', 'db' => 'PowerV2_Reporting_Dialer-17', 'tz' => 'Eastern Standard Time', 'created_pw' => '1', 'fpass_token' => 'Im2mXVPBfAv8fYYpIK42NEBUd5X8xw', 'token_created' => '2019-08-27 19:14:50', 'app_token' => '180c620c3d5a2ee48d58d73e799c0071', 'password_updated' => '2019-08-13 20:25:41', 'additional_dbs' => '', 'persist_filters' => NULL),
    array('user_id' => '95', 'user_type' => 'client', 'group_id' => '777', 'name' => 'Dev Demo', 'email' => 'devdemo@chase.com', 'password' => '098f6bcd4621d373cade4e832627b4f6', 'db' => 'PowerV2_Reporting_Dialer-17', 'tz' => 'Eastern Standard Time', 'created_pw' => '1', 'fpass_token' => NULL, 'token_created' => NULL, 'app_token' => '04c80a923cc9e84d8e102f115170af22', 'password_updated' => '2019-08-19 19:10:18', 'additional_dbs' => '', 'persist_filters' => '{"campaign":[]}'),
    array('user_id' => '96', 'user_type' => 'client', 'group_id' => '777', 'name' => 'Bryan Burchfield', 'email' => 'bryan.burchfield@chasedatacorp.com', 'password' => 'b186101d2b9e6c39cfc7a1132281bfe6', 'db' => 'PowerV2_Reporting_Dialer-17', 'tz' => 'Easter Island Standard Time', 'created_pw' => '1', 'fpass_token' => 'R7UuiEYXqsFDvU9KU3l5AcmUqEVYaN', 'token_created' => '2019-08-27 19:09:23', 'app_token' => '8246a892cd455d884ebeba185bde28f8', 'password_updated' => '2019-08-27 19:05:52', 'additional_dbs' => '', 'persist_filters' => NULL),
    array('user_id' => '84', 'user_type' => 'client', 'group_id' => '12345', 'name' => 'thomas test', 'email' => 'thomasgis@outlook.com', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99', 'db' => 'PowerV2_Reporting_Dialer-05', 'tz' => 'W. Central Africa Standard Time', 'created_pw' => '0', 'fpass_token' => NULL, 'token_created' => NULL, 'app_token' => 'ed4e0bda3c018610bd9b4397363093fa', 'password_updated' => NULL, 'additional_dbs' => '', 'persist_filters' => NULL),
    array('user_id' => '38', 'user_type' => 'client', 'group_id' => '211994', 'name' => 'rasanileads', 'email' => 'gair@rasanileads.com', 'password' => '5994b92e8a03fd43435ee47ef737c6b5', 'db' => 'PowerV2_Reporting_Dialer-08', 'tz' => 'Eastern Standard Time (Mexico)', 'created_pw' => '1', 'fpass_token' => NULL, 'token_created' => NULL, 'app_token' => '385b60868c8cf24e5a2b6d888790909c', 'password_updated' => '2019-06-24 19:23:26', 'additional_dbs' => '', 'persist_filters' => '{"campaign":[]}'),
    array('user_id' => '39', 'user_type' => 'client', 'group_id' => '212093', 'name' => 'Matt Bowers', 'email' => 'm.bowers@mydynamic1.com', 'password' => '81a7f4e5282aa6fe6942fbcc7b2ba1b7', 'db' => 'PowerV2_Reporting_Dialer-14', 'tz' => 'Central America Standard Time', 'created_pw' => '1', 'fpass_token' => NULL, 'token_created' => NULL, 'app_token' => '', 'password_updated' => '2019-06-25 20:30:11', 'additional_dbs' => '', 'persist_filters' => NULL),
    array('user_id' => '3', 'user_type' => '', 'group_id' => '212182', 'name' => '', 'email' => 'test2@chase.com', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99', 'db' => 'PowerV2_Reporting_Dialer-23', 'tz' => 'Pacific Standard Time', 'created_pw' => '1', 'fpass_token' => 'EwiPxOhS03MXMg2F7nixcXFUufJ5wD', 'token_created' => '2019-03-19 17:37:29', 'app_token' => 'C405F5C7E88DB5B70E438F16FA7E3828', 'password_updated' => '2019-03-19 16:13:58', 'additional_dbs' => '', 'persist_filters' => NULL),
    array('user_id' => '32', 'user_type' => 'client', 'group_id' => '235773', 'name' => 'BT Marketing', 'email' => 'btmarketing@chasedatacorp.com', 'password' => '124debb40e0dfe02ad5cb1fd0b4c33c7', 'db' => 'PowerV2_Reporting_Dialer-07', 'tz' => 'Eastern Standard Time', 'created_pw' => '1', 'fpass_token' => NULL, 'token_created' => NULL, 'app_token' => 'daf1cd29cb2e2b1ed069ef68847b15ff', 'password_updated' => '2019-05-28 16:02:05', 'additional_dbs' => 'PowerV2_Reporting_Dialer-15', 'persist_filters' => '{"campaign":[]}'),
    array('user_id' => '97', 'user_type' => 'client', 'group_id' => '236316', 'name' => 'system six', 'email' => 'six@chase.com', 'password' => '098f6bcd4621d373cade4e832627b4f6', 'db' => 'PowerV2_Reporting_Dialer-06', 'tz' => 'Eastern Standard Time', 'created_pw' => '1', 'fpass_token' => NULL, 'token_created' => NULL, 'app_token' => 'f3381162ab5b97ac323813d413c1f450', 'password_updated' => '2019-09-25 20:03:47', 'additional_dbs' => '', 'persist_filters' => NULL),
    array('user_id' => '94', 'user_type' => 'client', 'group_id' => '665123', 'name' => 'lasttest afterupdate', 'email' => 'asdfasdffd@adfadsf.com', 'password' => '5f4dcc3b5aa765d61d8327deb882cf99', 'db' => 'PowerV2_Reporting_Dialer-01', 'tz' => 'Azores Standard Time', 'created_pw' => '0', 'fpass_token' => NULL, 'token_created' => NULL, 'app_token' => '0155887af28409177e7fe10803e2b2a7', 'password_updated' => NULL, 'additional_dbs' => '', 'persist_filters' => NULL)
);

foreach ($users as $olduser) {
    // See if that user email already exists
    $user = User::where('email', $olduser['email'])->first();

    // Create if not already here
    if (!$user) {
        echo $olduser['email'] . "\n";

        $user = new User();
        $user->name = $olduser['name'];
        $user->email = $olduser['email'];
        $user->password = $olduser['password'];
        $user->user_type = $olduser['user_type'];
        $user->group_id = $olduser['group_id'];
        $user->db = $olduser['db'];
        $user->tz = $olduser['tz'];
        $user->app_token = $olduser['app_token'];
        $user->additional_dbs = $olduser['additional_dbs'];

        $user->save();
    }
}
