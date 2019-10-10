<?php

namespace App\Scripts;

use App\User;

// do a php export of the users table from the old db and paste here
$users = array();

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
