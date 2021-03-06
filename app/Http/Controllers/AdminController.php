<?php

namespace App\Http\Controllers;

use App\Http\Requests\DemoUser;
use App\Http\Requests\StandardUser;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AutomatedReport;
use App\Models\Dialer;
use App\Models\Recipient;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Traits\SqlServerTraits;
use App\Traits\TimeTraits;
use Illuminate\View\View;
use Illuminate\Contracts\View\Factory;

class AdminController extends Controller
{
    use TimeTraits;
    use SqlServerTraits;

    /**
     * Index
     * 
     * @param Request $request 
     * @return Illuminate\View\View|Illuminate\Contracts\View\Factory 
     */
    public function manageUsers(Request $request)
    {
        $groupId = Auth::user()->group_id;
        $this->setDb();

        $tot_client_count = 0;
        $tot_user_count = User::whereNotIn('user_type', ['demo', 'expired'])
            ->join('dialers', 'users.db', '=', 'dialers.reporting_db')
            ->where('group_id', Auth::User()->group_id)
            ->where('password', '!=', 'SSO')
            ->count();

        if (Auth::User()->isType('superadmin')) {
            $tot_client_count = User::whereNotIn('user_type', ['demo', 'expired'])->distinct('group_id')
                ->join('dialers', 'users.db', '=', 'dialers.reporting_db')
                ->where('password', '!=', 'SSO')
                ->count();
            $tot_user_count = User::whereNotIn('user_type', ['demo', 'expired'])
                ->join('dialers', 'users.db', '=', 'dialers.reporting_db')
                ->where('password', '!=', 'SSO')
                ->count();
        }

        $page['menuitem'] = 'manage_users';
        $page['sidenav'] = 'admin';
        $page['type'] = 'page';
        $data = [
            'page' => $page,
            'timezone_array' => $this->timezones(),
            'group_id' => $groupId,
            'tot_client_count' => $tot_client_count,
            'tot_user_count' => $tot_user_count,
            'dbs' => $this->dbs(),
            'dialers' => $this->dialers(),
            'user_types' => $this->userTypes(),
            'jsfile' => [],
            'demo_users' => User::whereIn('user_type', ['demo', 'expired'])->get()
        ];

        return view('admin.index')->with($data);
    }

    public function settings()
    {
        $page['menuitem'] = 'settings';
        $page['sidenav'] = 'admin';
        $page['type'] = 'page';
        $data = [
            'page' => $page,
            'timezone_array' => $this->timezones(),
            'dbs'  => $this->dbs(),
            'jsfile' => [],
        ];

        return view('admin.settings')->with($data);
    }

    /**
     * Return admin sidenav
     * 
     * @return View|Factory 
     */
    public function loadSidenav(Request $request)
    {
        $sidenav = '';

        if ($request->has('sidenav')) {
            $sidenav = '.' . $request->sidenav;
        }
        return view('shared.sidenav' . $sidenav);
    }

    public function loadCdrLookup()
    {
        $page['menuitem'] = 'cdr_lookup';
        $page['sidenav'] = 'tools';
        $page['type'] = 'page';
        $data = [
            'page' => $page,
            'jsfile' => [],
        ];

        return view('admin.cdr_lookup')->with($data);
    }

    public function webhookGenerator()
    {
        $page['menuitem'] = 'webhook_generator';
        $page['sidenav'] = 'tools';
        $page['type'] = 'page';
        $data = [
            'page' => $page,
            'dbs' => $this->dbs(),
            'jsfile' => [],
            'default_lead_fields' => $this->defaultLeadFields(),
        ];

        return view('admin.webhook_generator')->with($data);
    }

    public function accountingReports()
    {
        $page['menuitem'] = 'accounting_reports';
        $page['sidenav'] = 'tools';
        $page['type'] = 'page';
        $data = [
            'page' => $page,
            'jsfile' => [],
            'default_lead_fields' => $this->defaultLeadFields(),
        ];

        return view('tools.accounting_reports')->with($data);
    }

    /**
     * Set DB
     *  
     * @param string|null $db 
     * @return void 
     */

    private function setDb($db = null)
    {
        if (empty($db)) {
            $db = Auth::user()->db;
        }
        config(['database.connections.sqlsrv.database' => $db]);
    }

    private function dbs()
    {
        $dbs = ['' => trans('general.select_one')];

        foreach (Dialer::orderBy('dialer_numb')->get() as $dialer) {
            $dbs[$dialer->reporting_db] = $dialer->reporting_db;
        }

        return $dbs;
    }

    private function dialers()
    {
        if (Auth::user()->isType('superadmin')) {
            $dialers = Dialer::orderBy('dialer_numb')->get();
        } else {
            $dialers = Dialer::where('reporting_db', Auth::User()->db)
                ->orderBy('dialer_numb')
                ->get();
        }

        return $dialers;
    }

    public function userTypes()
    {
        // Build user type selection
        $user_types = [
            'client' => 'Client',
            'admin' => 'Admin',
        ];
        if (Auth::User()->isType('superadmin')) {
            $user_types += ['superadmin' => 'SuperAdmin'];
        }

        return $user_types;
    }

    /**
     * Add User
     * 
     * @param Request $request 
     * @return array 
     */
    public function addUser(StandardUser $request)
    {
        $input = $request->all();
        $input['password'] = Hash::make(uniqid());
        $input['app_token'] = $this->generateToken();

        $newuser = User::create($input);

        $newuser->sendWelcomeEmail($newuser);

        return ['success' => $newuser];
    }

    /**
     * Add Demo User (ajax)
     * 
     * @param DemoUser $request 
     * @return array
     * @throws mixed 
     */
    public function addDemoUser(DemoUser $request)
    {
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
                'db' => 'PowerV2_Reporting_Dialer-07',
                'tz' => 'Eastern Standard Time',
                'app_token' => $app_token,
                'expiration' => $expiration->toDateTimeString(),
                'password' => Hash::make($app_token),
            ])
        );

        // send welcome email unless demo_xxxxx@chasedatacorp.com
        if ($newuser->email != 'demo_' . $app_token . '@chasedatacorp.com') {
            $newuser->sendWelcomeDemoEmail($newuser);
        }

        return ['status' => 'success'];
    }

    /**
     * Generate Token
     * 
     * Creates unique app_token for user
     * 
     * @return string 
     */
    private function generateToken()
    {
        do {
            $hash = md5(uniqid());
        } while ($this->token_exists($hash));

        return $hash;
    }

    /**
     * Token Exists
     * 
     * Check if app_token exists in users table
     * 
     * @param string $hash 
     * @return App\Models\User 
     */
    private function token_exists($hash)
    {
        return User::where('app_token', $hash)->exists();
    }

    /**
     * Delete User (ajax)
     * 
     * @param Request $request 
     * @param bool $keep 
     * @return array
     */
    public function deleteUser(Request $request, $keep = false)
    {
        $user = User::findOrFail($request->id);

        // delete automated reports
        AutomatedReport::where('user_id', $user->id)->delete();

        // delete recipients if demo user
        if ($user->isType('demo')) {
            $this->deleteRecipients($user->id);
        }

        // delete user
        if (!$keep) {
            $user->delete();
        }

        return ['status' => 'user deleted'];
    }

    /**
     * Toggle User active/inactive (ajax)
     * 
     * @param Request $request 
     * @param bool $keep 
     * @return array
     */
    public function toggleUser(Request $request)
    {
        $user = User::findOrFail($request->id);

        if ($user->active) {  // deactivate
            // delete automated reports
            AutomatedReport::where('user_id', $user->id)->delete();

            // delete recipients if demo user
            if ($user->isType('demo')) {
                $this->deleteRecipients($user->id);
            }

            // deactivate user
            $user->active = false;
            $user->save();
        } else {  // activate user
            $user->active = true;
            $user->save();
        }

        return ['status' => 'success'];
    }

    /**
     * Delete Recipients
     * 
     * @param Integer $user_id 
     * @return void 
     */
    public function deleteRecipients($user_id)
    {
        $kpicontroller = new KpiController();
        $kpireq = new Request(['id' => 0]);

        foreach (Recipient::where('user_id', $user_id)->get() as $recipient) {
            $kpireq->replace(['id' => $recipient->id]);
            $kpicontroller->removeRecipient($kpireq);
        }
    }

    /**
     * Get User
     * 
     * @param Request $request 
     * @return App\Models\User 
     */
    public function getUser(Request $request)
    {
        return User::findOrFail($request->id);
    }

    /**
     * Update User
     * 
     * @param Request $request 
     * @return array 
     */
    public function updateUser(StandardUser $request)
    {
        $user = User::findOrFail($request->id);
        $user->update($request->all());

        return ['success' => $user];
    }

    /**
     * Update Demo User (ajax)
     * 
     * @param DemoUser $request 
     * @return array
     */
    public function updateDemoUser(DemoUser $request)
    {
        $user = User::findOrFail($request->id);

        if ($request->filled('expiration')) {
            $expiration = Carbon::now()->addDays($request->expiration);
            $request->merge([
                'expiration' => $expiration,
                'user_type' => 'demo'  // in case they were 'expired'
            ]);
        } elseif ($request->has('expiration')) {
            $request->request->remove('expiration');
        }

        $user->update($request->all());

        return ['status' => 'success'];
    }

    /**
     * Edit Myself
     * 
     * @param Request $request 
     * @return array
     */
    public function editMyself(Request $request)
    {
        try {
            $user = Auth::user();
            $user->update($request->all());

            // Logout, clear session, log back in
            Auth::logout();
            $request->session()->flush();
            Auth::login($user);
        } catch (Exception $e) {
            return ['errors' => ['Update Failed']];
        }

        return ['success' => 1];
    }

    /**
     * CDR Lookup
     * 
     * @param Request $request 
     * @return array 
     * @throws mixed 
     */
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
            FROM [PowerV2_Reporting_Dialer-" . sprintf("%02d", $dialer->dialer_numb) . "].[dbo].[DialingResults]
            WHERE CallDate BETWEEN @fromdate AND @todate
            AND $search_field = @phone
            AND CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD')";

            $union = "UNION";
        }

        $sql .= ") tmp";

        $bind = [
            'phone' => $phone,
            'fromdate' => $fromdate->format('Y-m-d H:i:s'),
            'todate' => $todate->format('Y-m-d H:i:s'),
        ];

        $results = $this->runSql($sql, $bind);

        if (count($results)) {
            // convert array of objects to array of arrays
            $results = json_decode(json_encode($results), true);
        }

        return [
            'columns' => $field_array,
            'search_result' => $results,
        ];
    }

    public function getClientTables(Request $request)
    {
        $bind = [
            'groupid' => $request->group_id,
        ];

        $sql = "SELECT TableName, Description
            FROM [AdvancedTables]
            WHERE GroupId = :groupid
            ORDER BY TableName";

        $result = $this->runSql($sql, $bind, $request->database);

        return ['tables' => $result];
    }

    public function getTableFields(Request $request)
    {
        $bind = [
            'table_name' => 'ADVANCED_' . $request->table_name,
        ];

        $sql = "SELECT COLUMN_NAME
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_NAME = :table_name
                ORDER BY ORDINAL_POSITION";

        $result = resultsToList($this->runSql($sql, $bind, $request->database));

        unset($result['LeadId']);

        return ['fields' => array_values($result)];
    }

    private function defaultLeadFields()
    {
        return [
            'ClientId',
            'FirstName',
            'LastName',
            'PrimaryPhone',
            'Address',
            'City',
            'State',
            'ZipCode',
            'Notes',
            'Campaign',
            'Subcampaign',
        ];
    }

    public function getGroups(Request $request)
    {
        return $this->getAllGroups($request->dialer);
    }

    private function getAllGroups($db)
    {
        $sql = "SELECT GroupId, GroupName
            FROM [$db].[dbo].[Groups]
            WHERE GroupId > -1
            AND IsActive = 1
            ORDER BY GroupName";

        $results = $this->runSql($sql);

        return $results;
    }
}
