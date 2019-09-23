<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\User;

trait DashTraits
{
    private $databases;
    private $campaign;
    private $dateFilter;
    private $inorout;
    public $isApi;
    public $curdash;

    public function apiLogin(Request $request)
    {
        // find first user record with that token
        $user = User::where('app_token', $request->token)->first();
        if ($user === null) {
            abort(403, 'Invalid token');
        }

        // Login that user and set session var so we know it's via API
        Auth::loginUsingId($user->id);
        session(['isApi' => 1]);

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

        $tz = Auth::user()->getIanaTz();

        $prevRecs = false;
        $delRecs = [];
        $fromDate = utcToLocal($params['fromDate'], $tz);
        $toDate = utcToLocal($params['toDate'], $tz)->modify('-1 second');

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
            return (new \DateTime($a['Time'])) > (new \DateTime($b['Time']));
        });

        return $result;
    }

    private function dateTimeToHour($rec)
    {
        // array_map target function
        $rec['Time'] = (new \DateTime($rec['Time']))->format('g:i');
        return $rec;
    }

    private function dateTimeToDay($rec)
    {
        // array_map target function
        $rec['Time'] = (new \DateTime($rec['Time']))->format('D n/j/y');
        return $rec;
    }

    private function filterDetails()
    {
        $tz = Auth::user()->getIanaTz();

        list($fromDate, $toDate) = $this->dateFilter;

        // convert to local and back toDate up a second
        $fromDate = utcToLocal($fromDate, $tz);
        $toDate = utcToLocal($toDate, $tz)->modify('-1 second');

        $cnt = count((array) $this->campaign);

        if (empty($this->campaign)) {
            $campaign = "All Campaigns";
        } elseif ($cnt > 1) {
            $campaign = "$cnt Campaigns Selected";
        } else {
            $campaign = $this->campaign[0];
        }

        switch ($this->dateFilter) {
            case 'today':
                $today = $fromDate->format('n/j/y');
                $details = "Today | $today";
                break;
            case 'yesterday':
                $yesterday = $fromDate->format('n/j/y');
                $details = "Yesterday | $yesterday";
                break;
            case 'week':
                $monday = $fromDate->format('n/j/y');
                $sunday = $toDate->format('n/j/y');
                $details = $monday . ' - ' . $sunday . ' (This Week)';
                break;
            case 'last_week':
                $monday = $fromDate->format('n/j/y');
                $sunday = $toDate->format('n/j/y');
                $details = $monday . ' - ' . $sunday . ' (Last Week)';
                break;
            case 'month':
                $month = $fromDate->format('F Y');
                $details = "$month (MTD)";
                break;
            case 'last_month':
                $month = $fromDate->format('F Y');
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
        $tz = Auth::user()->getIanaTz();
        $todayLocal = utcToLocal(new \DateTime, $tz)->format('Y-m-d');

        // the $toDate is non-inclusive
        switch ($dateFilter) {
            case 'today':
                $fromDate = localToUtc($todayLocal, $tz);
                $toDate = new \DateTime;  // already UTC
                break;

            case 'yesterday':
                // all day yesterday
                $toDate = localToUtc(utcToLocal(new \DateTime, $tz)->format('Y-m-d'), $tz);
                $fromDate = (clone $toDate)->modify('-1 day');
                break;

            case 'week':
                // from monday thru sunday -- this will always include future datetimes
                $fromDate = localToUtc((new \DateTime($todayLocal))->modify('Monday this week'), $tz);
                $toDate = (clone $fromDate)->modify('+1 week');
                break;

            case 'last_week':
                // from monday thru sunday -- this will always include future datetimes
                $fromDate = localToUtc((new \DateTime($todayLocal))->modify('Monday last week'), $tz);
                $toDate = (clone $fromDate)->modify('+1 week');
                break;

            case 'month':
                // from first day of this month at 00:00:00 to current date+time
                $fromDate = localToUtc(date('Y-m-1', strtotime($todayLocal)), $tz);
                $toDate = new \DateTime;  // already UTC
                break;

            case 'last_month':
                // from first day of last month at 00:00:00 to current date+time
                $toDate = localToUtc(date('Y-m-1', strtotime($todayLocal)), $tz);
                $fromDate = (clone $toDate)->modify('-1 month');
                break;

            default:  // custom range - add 1 day to ending date
                $fromDate = localToUtc(substr($dateFilter, 0, 10), $tz);
                $toDate = localToUtc(date('Y-m-d', strtotime('+1 day', strtotime(substr($dateFilter, 11)))), $tz);
        }

        return [$fromDate, $toDate];
    }

    public function previousDateRange($dateFilter)
    {
        $tz = Auth::user()->getIanaTz();
        $todayLocal = utcToLocal(new \DateTime, $tz)->format('Y-m-d');

        // the $toDate is non-inclusive
        switch ($dateFilter) {
            case 'today':
                // yesterday
                $toDate = localToUtc(utcToLocal(new \DateTime, $tz)->format('Y-m-d'), $tz);
                $fromDate = (clone $toDate)->modify('-1 day');
                break;

            case 'yesterday':
                // day before yesterday
                $toDate = localToUtc(utcToLocal(new \DateTime, $tz)->format('Y-m-d'), $tz)->modify('-1 day');
                $fromDate = (clone $toDate)->modify('-1 day');
                break;

            case 'week':
                // last week
                $fromDate = localToUtc((new \DateTime($todayLocal))->modify('Monday last week'), $tz);
                $toDate = (clone $fromDate)->modify('+1 week');
                break;

            case 'last_week':
                // week before last
                $fromDate = localToUtc((new \DateTime($todayLocal))->modify('Monday last week'), $tz)->modify('-1 week');
                $toDate = (clone $fromDate)->modify('+1 week');
                break;

            case 'month':
                // last month
                $toDate = localToUtc(date('Y-m-1', strtotime($todayLocal)), $tz);
                $fromDate = (clone $toDate)->modify('-1 month');
                break;

            case 'last_month':
                // month before last
                $toDate = localToUtc(date('Y-m-1', strtotime($todayLocal)), $tz)->modify('-1 month');
                $fromDate = (clone $toDate)->modify('-1 month');
                break;

            default:
                // same number of previous days
                $date1 = localToUtc(substr($dateFilter, 0, 10), $tz);
                $date2 = localToUtc(date('Y-m-d', strtotime('+1 day', strtotime(substr($dateFilter, 11)))), $tz);

                $days = (int) $date1->diff($date2)->format('%a');

                $fromDate = (clone $date1)->modify('-' . $days . ' days');
                $toDate = $date1;
        }

        // if previous date range is a single day and a Sunday, use Friday instead
        if ($fromDate->format('Y-m-d') == (clone $toDate)->modify('-1 day')->format('Y-m-d') && $fromDate->format('w') == '0') {
            $fromDate->modify('-2 days');
            $toDate->modify('-2 days');
        }

        return [$fromDate, $toDate];
    }

    private function runSql($sql, $bind)
    {
        $db = Auth::user()->db;
        config(['database.connections.sqlsrv.database' => $db]);

        try {
            $results = DB::connection('sqlsrv')->select(DB::raw($sql), $bind);
        } catch (\Exception $e) {
            $results = [];
        }

        if (count($results)) {
            // convert array of objects to array of arrays
            $results = json_decode(json_encode($results), true);
        }

        return $results;
    }

    private function runMultiSql($sql, $bind)
    {
        config(['database.connections.sqlsrv.database' => Auth::user()->db]);

        $pdo = DB::connection('sqlsrv')->getPdo();
        $stmt = $pdo->prepare($sql, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);

        foreach ($bind as $k => $v) {
            $stmt->bindValue($k, $v);
        }

        $stmt->execute();

        $result = [];

        do {
            $result[] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } while ($stmt->nextRowset());

        return $result;
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
        Log::info($request);

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
