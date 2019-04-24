<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\User;
use App\Campaign;

trait DashTraits
{
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
        Session::put(['isApi' => 1]);

        // And off we go!
        return redirect($request->route()->action['prefix']);
    }

    private function getSession()
    {
        // This won't work inside __construct()
        $this->campaign = Session::get('campaign', 'Total');
        $this->dateFilter = Session::get('dateFilter', 'today');
        $this->inorout = Session::get('inorout', 'inbound');
        $this->isApi = Session::get('isApi', 0);

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


            if ($found !== false && $params['byHour'] && (int)$fromDate->format('H') < 8 && !$prevRecs) {
                if (!array_diff($result[$found], $zeroRec)) {
                    array_unshift($delRecs, $found);
                    $found = false;
                }
            }
            if ($found === false) {
                if (($params['byHour'] && (int)$fromDate->format('H') >= 8) || $prevRecs) {
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

    public function updateFilters(Request $request)
    {
        $this->getSession();

        $this->campaign = $request->campaign;
        $this->dateFilter = $request->datefilter;
        $this->inorout = $request->inorout;

        Session::put([
            'campaign' => $this->campaign,
            'dateFilter' => $this->dateFilter,
            'inorout' => $this->inorout,
        ]);
    }

    private function filterDetails()
    {
        $tz = Auth::user()->tz;

        if (strpos($this->dateFilter, '/')) {
            $startDate = substr($this->dateFilter, 0, 10);
            $endDate = substr($this->dateFilter, 11);
        }

        if ($this->campaign == 'Total') {
            $this->campaign = 'All Campaigns';
        }
        if (!empty($this->campaign)) {
            $this->campaign = ' - <b>' . $this->campaign . '</b>';
        }

        $now = utcToLocal(new \DateTime, $tz)->format('n/j/y g:i A');

        switch ($this->dateFilter) {
                /// filter selection | time | campaign
            case 'today':
                $details = 'Today | ' . $now . $this->campaign;
                break;
            case 'yesterday':
                $yesterday = utcToLocal((new \DateTime)->modify('-1 day'), $tz)->format('n/j/y');
                $details = 'Yesterday | ' . $yesterday . $this->campaign;
                break;
            case 'week':
                $monday = (new \DateTime(date('Y-m-d', strtotime('monday this week'))))->format('n/j/y');
                $sunday = (new \DateTime(date('Y-m-d', strtotime('sunday this week'))))->format('n/j/y');
                $details = $monday . ' - ' . $sunday . ' (This Week) | ' . $now . $this->campaign;
                break;
            case 'last_week':
                $monday = (new \DateTime(date('Y-m-d', strtotime('monday last week'))))->format('n/j/y');
                $sunday = (new \DateTime(date('Y-m-d', strtotime('sunday last week'))))->format('n/j/y');
                $details = $monday . ' - ' . $sunday . ' (Last Week) | ' . $now . $this->campaign;
                break;
            case 'month':
                $month = (new \DateTime())->format('F Y');
                $details = $month . ' (MTD) | ' . $now . $this->campaign;
                break;
            case 'last_month':
                $month = ((new \DateTime())->modify('-1 month'))->format('F Y');
                $details = $month . ' | ' . $now . $this->campaign;
                break;
            default:
                $start = (new \DateTime($startDate))->format('n/j/y');
                $end = (new \DateTime($endDate))->format('n/j/y');

                $details = $start . ' - ' . $end . ' | ' . $now . $this->campaign;
        }

        return $details;
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
}
