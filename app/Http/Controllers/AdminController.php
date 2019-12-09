<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AutomatedReport;
use App\Models\Dialer;
use App\Models\Recipient;
use App\Models\System;
use App\Traits\TimeTraits;
use Exception;
use Illuminate\Support\Carbon;
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

        $timezone_array = ['' => trans('general.select_one')];

        // Get US timezones first
        $timezones = System::whereIn(
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
            ->orderBy('current_utc_offset')
            ->get()
            ->toArray();

        foreach ($timezones as $tz) {
            $timezone_array[$tz['name']] = '[' . $tz['current_utc_offset'] . '] ' . $tz['name'];
        }

        // Now UTC for the UK
        $timezones = System::whereIn(
            'name',
            [
                'Greenwich Standard Time',
            ]
        )
            ->orderBy('current_utc_offset')
            ->get()
            ->toArray();

        foreach ($timezones as $tz) {
            $timezone_array[$tz['name']] = '[' . $tz['current_utc_offset'] . '] ' . $tz['name'];
        }

        // And Australia
        $timezones = System::whereIn(
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
            ->orderBy('current_utc_offset')
            ->get()
            ->toArray();

        foreach ($timezones as $tz) {
            $timezone_array[$tz['name']] = '[' . $tz['current_utc_offset'] . '] ' . $tz['name'];
        }

        // And then the rest
        $timezones = System::whereNotIn(
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
            ->orderBy('current_utc_offset')
            ->get()
            ->toArray();

        foreach ($timezones as $tz) {
            $timezone_array[$tz['name']] = '[' . $tz['current_utc_offset'] . '] ' . $tz['name'];
        }

        $dbs = ['' => trans('general.select_one')];

        foreach (Dialer::orderBy('dialer_numb')->get() as $dialer) {
            $dbs[$dialer->reporting_db] = $dialer->reporting_db;
        }

        $page['menuitem'] = 'admin';
        $page['type'] = 'page';
        $data = [
            'page' => $page,
            'timezone_array' => $timezone_array,
            'group_id' => $groupId,
            'dbs' => $dbs,
            'jsfile' => [],
            'demo_users' => User::where('user_type', 'demo')->get()
        ];

        return view('dashboards.admin')->with($data);
    }

    public function addUser(Request $request)
    {
        // check if name or email exists
        $existing_user = User::where('name', $request->name)
            ->orWhere('email', $request->email)
            ->first();

        if (!$existing_user) {
            $app_token = $this->generateToken();

            $input = $request->all();
            $input['password'] = Hash::make(uniqid());
            $newuser = User::create(array_merge($input, ['app_token' => $app_token]));

            $newuser->sendWelcomeEmail($newuser);

            $return['success'] = $newuser;
        } else {
            $return['errors'] = 'Name or email already in use by "' .
                $existing_user->name . '" in ' .
                $existing_user->db;
        }

        return $return;
    }

    public function addDemoUser(Request $request)
    {
        // check name exists
        $existing_user = User::where('name', $request->name)->first();

        if (!$existing_user) {
            $app_token = $this->generateToken();

            // If no email given, create one
            if (!$request->filled('email')) {
                $request->request->add(['email' => 'demo_' . $app_token . '@chasedatacorp.com']);
            }

            // Calculate expiration date
            $expiration = Carbon::now()->addDays($request->expiration);
            $request->request->remove('expiration');

            $newuser = User::create(
                array_merge($request->all(), [
                    'user_type' => 'demo',
                    'group_id' => '777',
                    'db' => 'PowerV2_Reporting_Dialer-17',
                    'tz' => 'Eastern Standard Time',
                    'app_token' => $app_token,
                    'expiration' => $expiration->toDateTimeString(),
                    'password' => Hash::make($app_token),
                ])
            );

            $newuser->sendWelcomeDemoEmail($newuser);

            return redirect('/dashboards/admin#demo_user');
        } else {
            $return['errors'] = 'Name already in use by "' .
                $existing_user->name . '" in ' .
                $existing_user->db;
        }

        return $return;
    }

    private function generateToken()
    {
        do {
            $hash = md5(uniqid());
        } while ($this->token_exists($hash));

        return $hash;
    }

    private function token_exists($hash)
    {
        return User::where('app_token', $hash)->exists();
    }

    public function deleteUser(Request $request)
    {
        $user = User::findOrFail($request->id);

        // delete automated reports
        AutomatedReport::where('user_id', $user->id)->delete();

        // delete recipients if demo user
        if ($user->isType('demo')) {
            $this->deleteRecipients($user->id);
        }

        // delete user
        $user->delete();

        return ['status' => 'user deleted'];
    }

    public function deleteRecipients($user_id)
    {
        $kpicontroller = new KpiController();
        $kpireq = new Request(['id' => 0]);

        foreach (Recipient::where('user_id', $user_id)->get() as $recipient) {
            $kpireq->replace(['id' => $recipient->id]);
            $kpicontroller->removeRecipient($kpireq);
        }
    }

    public function getUser(Request $request)
    {
        $user = User::findOrFail($request->id);

        /// if editing demo user
        if($request->mode == 'edit'){

            $datestr=$user->expiration;
            $date=strtotime($datestr);

            /// calculate difference
            $diff=$date-time();
            $days=floor($diff/(60*60*24));
            $hours=round(($diff-$days*60*60*24)/(60*60));

            /// demo expires
            $expiration_date = $days .' days '. $hours.' hours';

            return [
                'user' => $user,
                'expires' => $expiration_date
            ];
        }

        return $user;
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

            if ($request->has('expiration')) {
                $expiration = Carbon::now()->addDays($request->expiration);
                $request->merge(['expiration' => $expiration]);
            }

            $user->update($request->all());
            $return['success'] = $user;
        }

        return $return;
    }

    public function editMyself(Request $request)
    {
        try {
            $user = Auth::user();
            $user->update($request->all());
        } catch (Exception $e) {
            return ['errors' => ['Update Failed']];
        }

        return ['success' => 1];
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
        foreach (Dialer::orderBy('dialer_numb')->get() as $dialer) {
            $sql .= " $union
            SELECT " . $dialer->dialer_numb . " as [Server], $fields
			FROM [PowerV2_Reporting_Dialer-" . sprintf("%02d", $dialer->dialer_numb) . "].[dbo].[DialingResults] WHERE Date BETWEEN @fromdate AND @todate AND $search_field = @phone";

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
