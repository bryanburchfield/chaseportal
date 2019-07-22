<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\System;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class Admin extends Controller
{
    private function setDb($db = null)
    {
        if (empty($db)) {
            $db = Auth::user()->db;
        }
        config(['database.connections.sqlsrv.database' => $db]);
    }

    public function index(Request $request)
    {
        $groupId = Auth::user()->group_id;

        $this->setDb();
        $timezones = System::all()->sortBy('current_utc_offset')->toArray();

        $timezone_array = ['' => 'Select One'];
        foreach ($timezones as $tz) {
            $timezone_array[$tz['name']] = '[' . $tz['current_utc_offset'] . '] ' . $tz['name'];
        }

        $dbs = ['' => 'Select One'];
        for ($i = 0; $i <= 24; $i++) {
            $dbs['PowerV2_Reporting_Dialer-' . $i] = 'PowerV2_Reporting_Dialer-' . $i;
        }

        $users = User::all()->sortBy('id');

        $page['menuitem'] = 'admin';
        $page['type'] = 'page';
        $data = [
            'page' => $page,
            'timezone_array' => $timezone_array,
            'group_id' => $groupId,
            'dbs' => $dbs,
            'users' => $users,
            'jsfile' => [],
        ];

        return view('master.admin')->with($data);
    }

    public function addUser(Request $request)
    {
        $input = $request->all();
        $input['password'] = Hash::make('password');

        User::create($input);
        return redirect('master/admin');
    }

    public function deleteUser(Request $request)
    {
        $user = User::findOrFail($request->id)->delete();
        return redirect('master/admin');
    }

    public function getUser(Request $request)
    {
        $user = User::findOrFail($request->id);

        return $user;
    }

    public function updateUser(Request $request)
    {
        /// check if user name or email exists
        $user_check = User::where('name', $request->name)
            ->orWhere('email', $request->email)->first();

        if($user_check->id == $request->id){
            $user = User::findOrFail($request->id);
            $input = $request->all();
            $user->update($input);
            return 'true';
        }else{
            return 'User already exists';
        }
        
    }
}
