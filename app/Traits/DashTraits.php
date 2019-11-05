<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use Illuminate\Support\Carbon;

trait DashTraits
{
    private $databases;
    private $campaign;
    private $dateFilter;
    private $inorout;
    private $rep;
    public $isApi;
    public $curdash;

    use SqlServerTraits;
    use TimeTraits;

    public function apiLogin(Request $request)
    {
        // Allow either of these url formats:
        // http://.../?app_token=XXXXXXXXX&rep=RepName
        // http://.../api/XXXXXXXXX/RepName

        if ($request->has('app_token')) {
            $token = $request->app_token;
            $rep = $request->rep;
        } else {
            $token = $request->token;
            $rep = $request->rep;
        }

        // find first user record with that token
        $user = User::where('app_token', $token)->first();
        if ($user === null) {
            abort(403, 'Invalid token');
        }

        // Login that user and set session var so we know it's via API
        Auth::loginUsingId($user->id);
        session(['isApi' => 1]);

        if (isset($rep)) {
            $this->rep = $request->rep;
            session(['rep' => $this->rep]);
        }

        // And off we go!
        // return redirect($request->route()->action['prefix']);
        return $this->index($request);
    }

    private function getSession(Request $request)
    {
        // Check if there's a 'campaign' session var
        // set from db if not
        if (!session()->has('campaign')) {
            $filters = (array) json_decode(Auth::user()->persist_filters);

            if (isset($filters['campaign'])) {
                $campaign = array_filter($filters['campaign']);
            } else {
                $campaign = '';
            }

            if (!empty($campaign)) {
                $this->campaign = $campaign;
            } else {
                $this->campaign = '';
            }
            session(['campaign' => $this->campaign]);
        }

        // This won't work inside __construct()
        $this->campaign = session('campaign', '');
        $this->databases = session('databases', []);
        $this->dateFilter = session('dateFilter', 'today');
        $this->inorout = session('inorout', 'inbound');
        $this->isApi = session('isApi', 0);
        $this->curdash = session('curdash', 'admindash');
        $this->rep = session('rep', '');

        if (empty($this->databases)) {
            $this->databases = Auth::user()->getDatabaseList();
            session(['databases' => $this->databases]);
        }

        // this is a bugfix for js bug
        if (empty($this->dateFilter)) {
            $this->dateFilter = 'today';
        }

        // set sqlsrv db up here too
        config(['database.connections.sqlsrv.database' => Auth::user()->db]);
    }

    /**
     * Create sql snippet for Campaign IN (....) clause
     *
     * @param string $table
     * @param array $campaign
     * @return [string, array]
     */
    private function campaignClause($table, $iteration, $campaign)
    {
        if (empty($campaign) || $campaign == 'Total') {
            return ['', []];
        }

        $where = "AND $table.Campaign IN (";
        $bind = [];

        foreach ((array) $campaign as $i => $camp) {
            $param = 'camp_' . $iteration . '_' . $i;
            $where .= ":$param,";
            $bind[$param] = $camp;
        }

        $where = substr($where, 0, -1) . ")";

        return [$where, $bind];
    }

    private function formatVolume($result, $params)
    {
        // define recs with no data to compare against or insert if we need to fill in gaps
        return ($this->zeroRecs($result, $params['zeroRec'], $params));
    }

    private function zeroRecs($result, $zeroRec, $params)
    {
        // loop thru looking for recs.  Insert empty recs if there
        // are gaps.  Delete empty recs if we're doing hourly and it's
        // before 8am
        // We'll use our from/to dates but convert them to local first
        // Subtract 1 second from end date since it'll be the start of the next day

        $tz = Auth::user()->iana_tz;

        $prevRecs = false;
        $delRecs = [];
        $fromDate = $this->utcToLocal($params['fromDate'], $tz);
        $toDate = $this->utcToLocal($params['toDate'], $tz)->modify('-1 second');

        while ($fromDate <= $toDate) {
            $loopDate = $fromDate->format($params['format']);
            $zeroRec['Time'] = $loopDate;

            // See if there's a rec for this timestamp
            $found = array_search($loopDate, array_column($result, 'Time'));

            // if it's all zeros, before 8am, and no prev data then delete it
            // we have to save the indexes to delete to an array, if we try to delete
            // them now we screw up the loop


            if ($found !== false && $params['byHour'] && (int) $fromDate->format('H') < 8 && !$prevRecs) {
                if (!array_diff($result[$found], $zeroRec)) {
                    array_unshift($delRecs, $found);
                    $found = false;
                }
            }
            if ($found === false) {
                if (($params['byHour'] && (int) $fromDate->format('H') >= 8) || $prevRecs) {
                    array_push($result, $zeroRec);
                    $prevRecs = true;
                }
            } else {
                $prevRecs = true;
            }

            $fromDate->modify($params['modifier']);
        }

        // now delete any we found
        foreach ($delRecs as $k) {
            unset($result[$k]);
        }

        // sort our array in case we pushed any recs in
        usort($result, function ($a, $b) {
            return (new Carbon($a['Time'])) > (new Carbon($b['Time']));
        });

        return $result;
    }

    private function dateTimeToHour($rec)
    {
        // array_map target function
        $rec['Time'] = Carbon::parse($rec['Time'])->format('g:i');
        return $rec;
    }

    private function dateTimeToDay($rec)
    {
        // array_map target function
        $rec['Time'] = Carbon::parse($rec['Time'])->format('D n/j/y');
        return $rec;
    }

    private function filterDetails()
    {
        $tz = Auth::user()->iana_tz;

        list($fromDate, $toDate) = $this->dateRange($this->dateFilter);

        // convert to local and back toDate up a second since it's not inclusive
        $fromDate = $this->utcToLocal($fromDate, $tz);
        $toDate = $this->utcToLocal($toDate, $tz)->modify('-1 second');

        $month = trans('general.' . strtolower($fromDate->format('F'))) .
            ' ' . $fromDate->format('Y');

        $cnt = count((array) $this->campaign);

        if (empty($this->campaign)) {
            $campaign = "All Campaigns";
        } elseif ($cnt > 1) {
            $campaign = $cnt . ' ' . trans('general.campaigns_selected');
        } else {
            $campaign = $this->campaign[0];
        }

        switch ($this->dateFilter) {
            case 'today':
                $today = $fromDate->format('n/j/y');
                $details = trans('general.today') . " | $today";
                break;
            case 'yesterday':
                $yesterday = $fromDate->format('n/j/y');
                $details = trans('general.yesterday') . " | $yesterday";
                break;
            case 'week':
                $monday = $fromDate->format('n/j/y');
                $sunday = $toDate->format('n/j/y');
                $details = $monday . ' - ' . $sunday . ' (' . trans('general.this_week') . ')';
                break;
            case 'last_week':
                $monday = $fromDate->format('n/j/y');
                $sunday = $toDate->format('n/j/y');
                $details = $monday . ' - ' . $sunday . ' (' . trans('general.last_week') . ')';
                break;
            case 'month':
                $details = "$month (MTD)";
                break;
            case 'last_month':
                $details = $month;
                break;
            default:
                $start = $fromDate->format('n/j/y');
                $end = $toDate->format('n/j/y');
                $details = $start . ' - ' . $end;
        }

        return [$campaign, $details];
    }

    private function dateRange($dateFilter)
    {
        $tz = Auth::user()->iana_tz;

        // the $toDate is non-inclusive
        // returns UTC dates
        switch ($dateFilter) {
            case 'today':
                $fromDate = Carbon::parse('today', $tz)->tz('UTC');
                $toDate = new Carbon();  // already UTC
                break;

            case 'yesterday':
                // all day yesterday
                $fromDate = Carbon::parse('yesterday', $tz)->tz('UTC');
                $toDate = Carbon::parse('today', $tz)->tz('UTC');
                break;

            case 'week':
                // from monday thru current
                $fromDate = Carbon::parse('Monday this week', $tz)->tz('UTC');
                $toDate = new Carbon();  // already UTC
                break;

            case 'last_week':
                // from monday thru sunday
                $fromDate = Carbon::parse('Monday last week', $tz)->tz('UTC');
                $toDate = Carbon::parse('Monday this week', $tz)->tz('UTC');
                break;

            case 'month':
                // from first day of this month at 00:00:00 to current date+time
                $fromDate = Carbon::parse('midnight first day of this month', $tz)->tz('UTC');
                $toDate = new Carbon();  // already UTC
                break;

            case 'last_month':
                // from first day of last month at 00:00:00 to current date+time
                $fromDate = Carbon::parse('midnight first day of last month', $tz)->tz('UTC');
                $toDate = Carbon::parse('midnight first day of this month', $tz)->tz('UTC');
                break;

            default:  // custom range - add 1 day to ending date
                $fromDate = Carbon::parse(substr($dateFilter, 0, 10), $tz)->tz('UTC');
                $toDate = Carbon::parse(substr($dateFilter, 11), $tz)->modify('+1 day')->tz('UTC');
        }

        return [$fromDate, $toDate];
    }

    /**
     * Previous date range
     *
     * If current date range is a single day (today, yesterday, or custom) then
     * the previous range will be the same day of week, the week before.
     *
     * @param string $dateFilter
     * @return void
     */
    public function previousDateRange($dateFilter)
    {
        $tz = Auth::user()->iana_tz;

        // the $toDate is non-inclusive
        switch ($dateFilter) {
            case 'today':
                // same partial day last week
                $fromDate = Carbon::parse('midnight -1 week', $tz)->tz('UTC');
                $toDate = Carbon::parse('-1 week');
                break;

            case 'yesterday':
                // same full day prior week
                $fromDate = Carbon::parse('midnight -8 days', $tz)->tz('UTC');
                $toDate = Carbon::parse('midnight -1 week', $tz)->tz('UTC');
                break;

            case 'week':
                // last monday thru same partial day last week
                $fromDate = Carbon::parse('monday last week', $tz)->tz('UTC');
                $toDate = Carbon::parse('-1 week');
                break;

            case 'last_week':
                // two full weeks ago
                $fromDate = Carbon::parse('monday -3 weeks', $tz)->tz('UTC');
                $toDate = Carbon::parse('monday -2 weeks', $tz)->tz('UTC');
                break;

            case 'month':
                // 1st day of last month thru current number of seconds into this month
                $secsMonth = (Carbon::parse()->tz($tz))->diffInSeconds(new Carbon('midnight first day of this month', $tz));
                $fromDate = Carbon::parse('midnight first day of last month', $tz)->tz('UTC');
                $toDate = Carbon::parse($fromDate)->modify('+' . $secsMonth . 'seconds');
                break;

            case 'last_month':
                // full month before last
                $fromDate = Carbon::parse('midnight first day of -2 months', $tz)->tz('UTC');
                $toDate = Carbon::parse('midnight first day of last month', $tz)->tz('UTC');
                break;

            default:  // custom range
                // same number of previous days
                $date1 = Carbon::parse(substr($dateFilter, 0, 10), $tz)->tz('UTC');
                $date2 = Carbon::parse(substr($dateFilter, 11), $tz)->modify('+1 day')->tz('UTC');

                $days = $date2->diffInDays($date1);

                $fromDate = (clone $date1)->modify('-' . $days . ' days');
                $toDate = $date1;

                // if custom date range is a single day, compare to the week before
                if ($days == 1) {
                    $fromDate->modify('-6 days');
                    $toDate->modify('-6 days');
                }
        }

        return [$fromDate, $toDate];
    }

    public function byHour($dateFilter)
    {
        $byHour = ($dateFilter == 'today' || $dateFilter == 'yesterday') ? true : false;

        if (strlen($dateFilter) > 10) {
            if (substr($dateFilter, 0, 10) == substr($dateFilter, 11)) {
                $byHour = true;
            }
        }

        return $byHour;
    }

    public function updateFilters(Request $request)
    {
        $filters = [
            'databases',
            'campaign',
            'dateFilter',
            'inorout',
        ];

        foreach ($filters as $filter) {
            if (isset($request->$filter)) {
                $val = $request->input($filter);
                if (is_array($val)) {
                    $val = array_filter($val);
                }
                session([$filter => $val]);
            }
        }

        Auth::user()->persistFilters($request);

        return ['campaigns' => $this->campaignGroups()];
    }

    public function campaignSearch(Request $request)
    {
        return ['search_result' => $this->campaignGroups(trim($request->get('query')))];
    }

    public function campaignGroups($partial = null)
    {
        $request = new Request();

        $this->getSession($request);

        list($fromDate, $toDate) = $this->dateRange($this->dateFilter);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $sql = '';
        $union = '';

        foreach ($this->databases as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['startdate' . $i] = $startDate;
            $bind['enddate' . $i] = $endDate;

            $sql .= "$union SELECT DISTINCT Campaign
            FROM [$db].[dbo].[DialingResults]
            WHERE GroupId = :groupid$i
            AND Campaign != ''
            AND Date >= :startdate$i
            AND Date < :enddate$i";

            if (!empty($partial)) {
                $bind['name' . $i] = $partial . '%';
                $sql .= " AND Campaign LIKE :name$i";
            }

            $union = ' UNION';
        }

        $result = $this->runSql($sql, $bind);

        $result = array_column($result, 'Campaign');

        if (empty($this->campaign)) {
            $selected = [];
        } else {
            $selected = (array) $this->campaign;
        }

        // add any selected camps that aren't in the result set
        foreach ($selected as $camp) {
            if (!in_array($camp, $result)) {
                $result[] = $camp;
            }
        }

        natcasesort($result);

        $camparray = [];

        $camparray[] = [
            'name' => 'All Campaigns',
            'value' => '',
            'selected' => empty($selected) ? 1 : 0,
        ];

        foreach ($result as $camp) {
            $camparray[] = [
                'name' => $camp,
                'value' => $camp,
                'selected' => in_array($camp, $selected) ? 1 : 0,
            ];
        }

        return $camparray;
    }

    public function getDatabaseArray()
    {
        $dblist = [];
        foreach (Auth::user()->getDatabaseArray() as $dbname => $db) {
            $dblist[] = [
                'database' => $db,
                'name' => $dbname,
                'selected' => in_array($db, $this->databases) ? 1 : 0,
            ];
        }

        return $dblist;
    }
}
