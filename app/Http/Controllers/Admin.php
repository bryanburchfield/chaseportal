<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\System;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
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
        for ($i = 1; $i <= 25; $i++) {
            if ($i == 13) continue;  // there is no db 13
            $dbs['PowerV2_Reporting_Dialer-' . sprintf("%02d", $i)] = 'PowerV2_Reporting_Dialer-' . sprintf("%02d", $i);
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

    public function token_exists($hash)
    {
        // $sql = "SELECT COUNT(*) FROM users WHERE app_token = :hash";
        // $bind = ['hash' => $hash];

        // return $this->mySqlDb->fetchValue($sql, $bind);

        return $token = User::where('app_token', $hash)->exists();
    }

    public function addUser(Request $request)
    {

        do {
            $hash = md5(uniqid());
        }   while ($this->token_exists($hash));

        /// check if name or email exists
        $user_check = User::where('name', $request->name)
            ->orWhere('email', $request->email)
            ->first();

        if (!$user_check) {
            $input = $request->all();
            $input['password'] = Hash::make('password');
            $newuser = User::create($input);
            $return['success'] = $newuser;
        } else {
            $return['errors'] = 'Name or email already in use';
        }

        echo json_encode($return);
    }

    public function deleteUser(Request $request)
    {
        // delete automated reports too

        $user = User::findOrFail($request->id)->delete();
        $return['status'] = 'user deleted';
        echo json_encode($return);
    }

    public function getUser(Request $request)
    {
        return User::findOrFail($request->id);
    }

    public function updateUser(Request $request)
    {
        /// check if name or email is used by another user
        $user_check = User::where('id', '!=', $request->id)
            ->where(function ($query) use ($request) {
                $query->where('name', $request->name)
                    ->orWhere('email', $request->email);
            })
            ->first();


        if ($user_check) {
            $return['status'] = 'Name or email in use by another user';
        } else {
            $user = User::findOrFail($request->id);
            $user->update($request->all());
            $return['status'] = 'success';
        }
        echo json_encode($return);
    }

    public function cdrLookup(Request $request)
    {
        $phone = preg_replace("/[^0-9]/", "", $request->phone);
        $fromdate = (new \DateTime($request->fromdate))->format('Y-m-d H:i:s');
        $todate = (new \DateTime($request->todate))->format('Y-m-d H:i:s');

        if ($request->search_type == 'number_dialed') {
            $search_field = 'Phone';
        } else {
            $search_field = 'CallerId';
        }

        $field_array = [
            'id',
            'LeadId',
            'Phone',
            'Rep',
            'Date',
            'Campaign',
            'GroupId',
            'Attempt',
            'Duration',
            'CallStatus',
            'CallerId',
            'CallType',
            'Subcampaign',
            'CallDate',
        ];

        $fields = '';
        foreach ($field_array as $field) {
            $fields .= '[' . $field . '],';
        }
        $fields = substr($fields, 0, -1);

        array_unshift($field_array, 'Server');

        $sql = "DECLARE @phone varchar(50) = :phone;
		DECLARE @fromdate datetime = :fromdate;
		DECLARE @todate datetime = :todate;
		
		SELECT [Server], $fields
		FROM (";

        $union = '';
        for ($db = 1; $db <= 25; $db++) {
            if ($db == 13) continue;

            $sql .= " $union
			SELECT $db as [Server], $fields
			FROM [PowerV2_Reporting_Dialer-" . sprintf("%02d", $db) . "].[dbo].[DialingResults] WHERE Date BETWEEN @fromdate AND @todate AND $search_field = @phone";

            $union = "UNION";
        }

        $sql .= ") tmp";

        $bind = [
            'phone' => $phone,
            'fromdate' => $fromdate,
            'todate' => $todate,
        ];

        $this->setDb();

        try {
            $results = DB::connection('sqlsrv')->select(DB::raw($sql), $bind);
        } catch (\Exception $e) {
            $results = [];
        }

        if (count($results)) {
            // convert array of objects to array of arrays
            $results = json_decode(json_encode($results), true);
        }

        $return = [
            'columns' => $field_array,
            'search_result' => $results,
        ];

        echo json_encode($return);
    }
}
