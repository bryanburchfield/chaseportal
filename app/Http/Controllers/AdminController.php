<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\AutomatedReport;
use App\System;
use App\Traits\TimeTraits;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    use TimeTraits;

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

        $timezone_array = ['' => 'Select One'];

        // Get US timezones first
        $timezones = System::all()
            ->whereIn(
                'name',
                [
                    'Eastern Standard Time',
                    'Central Standard Time',
                    'Mountain Standard Time',
                    'Pacific Standard Time',
                    'Alaskan Standard Time',
                    'Hawaiian Standard Time',
                ]
            )
            ->sortBy('current_utc_offset')->toArray();

        foreach ($timezones as $tz) {
            $timezone_array[$tz['name']] = '[' . $tz['current_utc_offset'] . '] ' . $tz['name'];
        }

        // Now UTC for the UK
        $timezones = System::all()
            ->whereIn(
                'name',
                [
                    'Greenwich Standard Time',
                ]
            )
            ->sortBy('current_utc_offset')->toArray();

        foreach ($timezones as $tz) {
            $timezone_array[$tz['name']] = '[' . $tz['current_utc_offset'] . '] ' . $tz['name'];
        }

        // And Australia
        $timezones = System::all()
            ->whereIn(
                'name',
                [
                    'W. Australia Standard Time',
                    'Aus Central W. Standard Time',
                    'AUS Central Standard Time',
                    'E. Australia Standard Time',
                    'Cen. Australia Standard Time',
                    'AUS Eastern Standard Time',
                ]
            )
            ->sortBy('current_utc_offset')->toArray();

        foreach ($timezones as $tz) {
            $timezone_array[$tz['name']] = '[' . $tz['current_utc_offset'] . '] ' . $tz['name'];
        }

        // And then the rest
        $timezones = System::all()
            ->whereNotIn(
                'name',
                [
                    'Eastern Standard Time',
                    'Central Standard Time',
                    'Mountain Standard Time',
                    'Pacific Standard Time',
                    'Alaskan Standard Time',
                    'Hawaiian Standard Time',
                    'Greenwich Standard Time',
                    'W. Australia Standard Time',
                    'Aus Central W. Standard Time',
                    'AUS Central Standard Time',
                    'E. Australia Standard Time',
                    'Cen. Australia Standard Time',
                    'AUS Eastern Standard Time',
                ]
            )
            ->sortBy('current_utc_offset')->toArray();

        foreach ($timezones as $tz) {
            $timezone_array[$tz['name']] = '[' . $tz['current_utc_offset'] . '] ' . $tz['name'];
        }

        $dbs = ['' => 'Select One'];
        for ($i = 1; $i <= 25; $i++) {
            if ($i == 13) {
                continue;
            }  // there is no db 13
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

        return view('dashboards.admin')->with($data);
    }

    public function token_exists($hash)
    {
        return User::where('app_token', $hash)->exists();
    }

    public function addUser(Request $request)
    {
        do {
            $hash = md5(uniqid());
        } while ($this->token_exists($hash));

        /// check if name or email exists
        $existing_user = User::where('name', $request->name)
            ->orWhere('email', $request->email)
            ->first();

        if (!$existing_user) {
            $input = $request->all();
            $input['password'] = Hash::make(uniqid());
            $newuser = User::create(array_merge($input, ['app_token' => $hash]));

            $newuser->sendWelcomeEmail($newuser);

            $return['success'] = $newuser;
        } else {
            $return['errors'] = 'Name or email already in use by "' .
                $existing_user->name . '" in ' .
                $existing_user->db;
        }

        return $return;
    }

    public function deleteUser(Request $request)
    {
        $user = User::findOrFail($request->id);

        // delete automated reports
        AutomatedReport::where('user_id', $user->id)->delete();

        // delete user
        $user->delete();

        return ['status' => 'user deleted'];
    }

    public function getUser(Request $request)
    {
        return User::findOrFail($request->id);
    }

    public function updateUser(Request $request)
    {
        /// check if name or email is used by another user
        $existing_user = User::where('id', '!=', $request->id)
            ->where(function ($query) use ($request) {
                $query->where('name', $request->name)
                    ->orWhere('email', $request->email);
            })
            ->first();

        if ($existing_user) {
            $return['errors'] = 'Name or email already in use by "' .
                $existing_user->name . '" in ' .
                $existing_user->db;
        } else {
            $user = User::findOrFail($request->id);
            $user->update($request->all());
            $return['success'] = $user;
        }

        return $return;
    }

    public function cdrLookup(Request $request)
    {
        $tz = Auth::user()->iana_tz;

        $phone = preg_replace("/[^0-9]/", "", $request->phone);
        $fromdate = $this->localToUtc($request->fromdate, $tz);
        $todate = $this->localToUtc($request->todate, $tz);

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
            if ($db == 13) {
                continue;
            }

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

        return [
            'columns' => $field_array,
            'search_result' => $results,
        ];
    }
}