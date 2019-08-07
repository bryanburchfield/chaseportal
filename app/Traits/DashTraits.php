<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\User;

trait DashTraits
{
    private $databases;
    private $campaign;
    private $dateFilter;
    private $inorout;

    public function apiLogin(Request $request)
    {
        // find first user record with that token
        $user = User::select('id')->where('app_token', $request->token)->first();
        if ($user === null) {
            abort(403, 'Invalid token');
        }

        // Login that user and set session var so we know it's via API
        Auth::loginUsingId($user->id);
        session(['isApi' => 1]);

        // And off we go!
        return redirect($request->route()->action['prefix']);
    }

    private function getSession(Request $request)
    {
        // This won't work inside __construct()
        $this->databases = session('databases', []);
        $this->campaign = session('campaign', 'Total');
        $this->dateFilter = session('dateFilter', 'today');
        $this->inorout = session('inorout', 'inbound');
        $this->isApi = session('isApi', 0);
        $this->curdash = session('curdash', 'admindash');

        // set sqlsrv db up here too
        $db = Auth::user()->db;
        config(['database.connections.sqlsrv.database' => $db]);
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

        $tz = Auth::user()->tz;

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
        $rec['Time'] = (new \DateTime($rec['Time']))->format('n/j/y');
        return $rec;
    }

    private function filterDetails()
    {
        $tz = Auth::user()->tz;

        $campaign = ($this->campaign == 'Total' || $this->campaign == null) ? 'All Campaigns' : $this->campaign;

        if (strpos($this->dateFilter, '/')) {
            $startDate = substr($this->dateFilter, 0, 10);
            $endDate = substr($this->dateFilter, 11);
        }

        switch ($this->dateFilter) {
            case 'today':
                $details = 'Today | ' . utcToLocal(date('Y-m-d H:i:s'), $tz)->format('n/j/y');
                break;
            case 'yesterday':
                $details = 'Yesterday | ' . utcToLocal(date("Y-M-d H:i:s", strtotime('-1 day')), $tz)->format('n/j/y');
                break;
            case 'week':
                $monday = (new \DateTime(date('Y-m-d', strtotime('monday this week'))))->format('n/j/y');
                $sunday = (new \DateTime(date('Y-m-d', strtotime('sunday this week'))))->format('n/j/y');
                $details = $monday . ' - ' . $sunday . ' (This Week)';
                break;
            case 'last_week':
                $monday = (new \DateTime(date('Y-m-d', strtotime('monday last week'))))->format('n/j/y');
                $sunday = (new \DateTime(date('Y-m-d', strtotime('sunday last week'))))->format('n/j/y');
                $details = $monday . ' - ' . $sunday . ' (Last Week)';
                break;
            case 'month':
                $details = (new \DateTime())->format('F Y') . ' (MTD)';
                break;
            case 'last_month':
                $details = ((new \DateTime())->modify('-1 month'))->format('F Y');
                break;
            default:
                $start = (new \DateTime($startDate))->format('n/j/y');
                $end = (new \DateTime($endDate))->format('n/j/y');
                $details = $start . ' - ' . $end;
        }

        return [$campaign, $details];
    }

    private function dateRange($dateFilter)
    {
        $tz = Auth::user()->tz;

        // the $toDate is non-inclusive
        switch ($dateFilter) {
            case 'today':
                // from today at 00:00 to current date+time
                $fromDate = localToUtc(new \DateTime(date('Y-m-d')), $tz);
                $toDate = new \DateTime;  // already UTC
                break;

            case 'yesterday':
                // all day yesterday
                $fromDate = localToUtc((new \DateTime(date('Y-m-d')))->modify('-1 day'), $tz);
                $toDate = localToUtc(new \DateTime(date('Y-m-d')), $tz);
                break;

            case 'week':
                // from monday thru sunday -- this will always include future datetimes
                $fromDate = localToUtc(new \DateTime(date('Y-m-d', strtotime('monday this week'))), $tz);
                $toDate = localToUtc(new \DateTime(date('Y-m-d', strtotime('monday next week'))), $tz);
                break;

            case 'last_week':
                // from monday thru sunday -- this will always include future datetimes
                $fromDate = localToUtc(new \DateTime(date('Y-m-d', strtotime('monday last week'))), $tz);
                $toDate = localToUtc(new \DateTime(date('Y-m-d', strtotime('monday this week'))), $tz);
                break;

            case 'month':
                // from first day of this month at 00:00:00 to current date+time
                $fromDate = localToUtc(new \DateTime(date('Y-m-1')), $tz);
                $toDate = new \DateTime;  // already UTC
                break;

            case 'last_month':
                // from first day of last month at 00:00:00 to current date+time
                $fromDate = localToUtc((new \DateTime(date('Y-m-1')))->modify('-1 month'), $tz);
                $toDate = localToUtc(new \DateTime(date('Y-m-1')), $tz);
                break;

            default:  // custom range - add 1 to ending date
                $fromDate = localToUtc(new \DateTime(substr($dateFilter, 0, 10)), $tz);
                $toDate = localToUtc((new \DateTime(substr($dateFilter, 11)))->modify('+1 day'), $tz);
        }

        return [$fromDate, $toDate];
    }

    public function previousDateRange($dateFilter)
    {
        $tz = Auth::user()->tz;

        // the $toDate is non-inclusive
        switch ($dateFilter) {
            case 'today':
                // yesterday
                $fromDate = localToUtc(date('Y-m-d', strtotime('-1 day')), $tz);
                $toDate = (new \DateTime)->modify('-1 day');
                break;

            case 'yesterday':
                // day before yesterday
                $fromDate = localToUtc(date('Y-m-d', strtotime('-2 days')), $tz);
                $toDate = localToUtc(date('Y-m-d', strtotime('-1 day')), $tz);
                break;

            case 'week':
                // last week
                $fromDate = localToUtc(date('Y-m-d', strtotime('monday last week')), $tz);
                $toDate = $toDate = (new \DateTime)->modify('-7 days');
                break;

            case 'last_week':
                // week before last
                $fromDate = localToUtc(date('Y-m-d', strtotime('monday last week')), $tz)->modify('-7 days');
                $toDate = localToUtc(date('Y-m-d', strtotime('monday this week')), $tz)->modify('-7 days');
                break;

            case 'month':
                // last month
                $fromDate = localToUtc(date('Y-m-1', strtotime('-1 month', strtotime(date('Y-m-1')))), $tz);
                $toDate = $toDate = (new \DateTime)->modify('-1 month');
                break;

            case 'last_month':
                // month before last
                $fromDate = localToUtc(date('Y-m-1', strtotime('-2 months', strtotime(date('Y-m-1')))), $tz);
                $toDate = localToUtc(date('Y-m-1', strtotime('-1 month', strtotime(date('Y-m-1')))), $tz);
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
        $filters = [
            'databases',
            'campaign',
            'dateFilter',
            'inorout',
        ];

        foreach ($filters as $filter) {
            if (isset($request->$filter)) {
                session([$filter => $request->$filter]);
            }
        }

        return ['campaigns' => []];
    }
}
