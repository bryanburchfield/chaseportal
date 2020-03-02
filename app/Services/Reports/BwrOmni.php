<?php

namespace App\Services\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\ReportTraits;
use Illuminate\Support\Carbon;

class BwrOmni
{
    use ReportTraits;

    public function __construct()
    {
        $this->initilaizeParams();

        $this->params['reportName'] = 'reports.bwr_omni';
        $this->params['nostreaming'] = 1;
        $this->params['columns'] = [
            'Campaign' => 'reports.campaign',
            'Subcampaign' => 'reports.subcampaign',
            'TotalLeads' => 'reports.',
            'SalesPerAttempt' => 'reports.',
            'Callable' => 'reports.',
            'Dials' => 'reports.',
            'AvgAttempt' => 'reports.',
            'Connects' => 'reports.',
            'Contacts' => 'reports.',
            'Sales' => 'reports.',
            'SalesPerDial' => 'reports.',
            'ConversionRate' => 'reports.',
            'ManHours' => 'reports.',
            'DialsPerManHour' => 'reports.',
            'ConnectsPerManHour' => 'reports.',
            'ContactsPerManHour' => 'reports.',
            'SalesPerManHour' => 'reports.',
            'WaitingTimeSec' => 'reports.',
            'AvgWaitingTimeSec' => 'reports.',
            'CallTimeSec' => 'reports.',
            'AvgCallTimeSec' => 'reports.',
            'PausedTimeSec' => 'reports.pausedtimesec',
            'DispositionTimeSec' => 'reports.pausedtimesec',
        ];
    }

    public function getFilters()
    {
        $filters = [
            'campaigns' => $this->getAllCampaigns(
                $this->params['fromdate'],
                $this->params['todate']
            ),
            'db_list' => Auth::user()->getDatabaseArray(),
        ];

        return $filters;
    }

    private function executeReport($all = false)
    {
        $this->setHeadings();

        list($fromDate, $toDate) = $this->dateRange($this->params['fromdate'], $this->params['todate']);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $campaigns = str_replace("'", "''", implode('!#!', $this->params['campaigns']));

        $tz =  Auth::user()->tz;

        $bind = [
            'group_id1' => Auth::user()->group_id,
            'group_id2' => Auth::user()->group_id,
            'startdate' => $startDate,
            'enddate' => $endDate,
            'campaigns' => $campaigns,
        ];

        $sql = "SET NOCOUNT ON;
            CREATE TABLE #SelectedCampaign(CampaignName varchar(50) Primary Key);
            INSERT INTO #SelectedCampaign SELECT DISTINCT [value] from dbo.SPLIT(:campaigns, '!#!');
            
            SELECT
                Campaign,
                Subcampaign,
                COUNT(*) as TotalLeads,
                CASE
                    WHEN SUM(Attempt)= 0 THEN 0
                    ELSE SUM(CASE WHEN Type = 3 THEN 1 ELSE 0 END) / SUM(CAST(Attempt as decimal))
                END as SalesPerAttempt,
                SUM(CAST(IsCallable as INT)) As Callable
            INTO #tmp_leads
            FROM (
                SELECT
                    L.Campaign,
                    IsNull(L.Subcampaign, '') as Subcampaign,
                    L.Attempt,
                    A.Data_Source_Primary,
                    A.Data_Source_Secondary,
                    A.Program,
                    D.Type,
                    IsNull((
                        SELECT TOP 1 D.IsCallable
                        FROM [Dispos] D
                        WHERE D.Disposition = L.CallStatus
                        AND (GroupId = L.GroupId OR IsSystem=1)
                        AND (Campaign = L.Campaign OR Campaign = '')
                        ORDER BY [id] Desc
                    ), 0) as IsCallable
                FROM Leads L
                LEFT OUTER JOIN ADVANCED_BWR_Master_Table A ON A.LeadID = L.IdGuid
                LEFT OUTER JOIN Dispos D ON D.Disposition = L.CallStatus AND D.GroupId = L.GroupId 
                WHERE L.GroupId = :group_id1
            ) tmp
            GROUP BY Campaign, Subcampaign;

            SELECT
                Campaign,
                Subcampaign,
                COUNT(*) as Dials,
                AVG(Attempt) as AvgAttempt,
                SUM(CASE WHEN Type > 0 THEN 1 ELSE 0 END) as Connects,
                SUM(CASE WHEN Type > 1 THEN 1 ELSE 0 END) as Contacts,
                SUM(CASE WHEN Type = 3 THEN 1 ELSE 0 END) as Sales,
                SUM(CASE WHEN Type = 3 THEN 1 ELSE 0 END) / CAST(COUNT(*) as decimal) as SalesPerDial,
                CASE 
                    WHEN SUM(CASE WHEN Type > 1 THEN 1 ELSE 0 END) = 0 THEN 0
                    ELSE SUM(CASE WHEN Type = 3 THEN 1 ELSE 0 END) / CAST(SUM(CASE WHEN Type > 1 THEN 1 ELSE 0 END) as decimal)
                END as ConversionRate
            INTO #tmp_calls
            FROM (
                SELECT 
                    DR.Campaign,
                    IsNull(DR.Subcampaign, '') as Subcampaign,
                    DR.Attempt,
                    D.Type
                FROM DialingResults DR
                LEFT OUTER JOIN Dispos D ON D.Disposition = DR.CallStatus AND D.GroupId = DR.GroupId 
                WHERE DR.GroupId = :group_id2
                AND DR.Date >= :startdate
                AND DR.Date < :enddate
            ) tmp
            GROUP BY Campaign, Subcampaign

            SELECT
                L.*,
                C.Dials,
                C.AvgAttempt,
                C.Connects,
                C.Contacts,
                C.Sales,
                C.SalesPerDial,
                C.ConversionRate 
            FROM #tmp_leads L
            INNER JOIN #tmp_calls C ON C.Campaign = L.Campaign AND C.Subcampaign = L.Subcampaign
            ORDER BY L.Campaign, L.Subcampaign";

        $results = $this->processResults($sql, $bind);

        if (empty($results)) {
            $this->params['totrows'] = 0;
            $this->params['totpages'] = 1;
            $this->params['curpage'] = 1;
            $results = [];
        } else {
            $this->params['totrows'] = count($results);
            $this->params['totpages'] = floor($this->params['totrows'] / $this->params['pagesize']);
            $this->params['totpages'] += floor($this->params['totrows'] / $this->params['pagesize']) == ($this->params['totrows'] / $this->params['pagesize']) ? 0 : 1;
        }

        return $this->getPage($results, $all);
    }

    private function processResults($sql, $bind)
    {
        // Add totals row after each campaign

        $results = [];
        $old_campaign = '';

        foreach ($this->yieldSql($sql, $bind) as $rec) {

            // Add fields to rec
            $rec['ManHours'] = '';
            $rec['DialsPerManHour'] = '';
            $rec['ConnectsPerManHour'] = '';
            $rec['ContactsPerManHour'] = '';
            $rec['SalesPerManHour'] = '';
            $rec['WaitingTimeSec'] = '';
            $rec['AvgWaitingTimeSec'] = '';
            $rec['CallTimeSec'] = '';
            $rec['AvgCallTimeSec'] = '';
            $rec['PausedTimeSec'] = '';
            $rec['DispositionTimeSec'] = '';

            if ($rec['Campaign'] != $old_campaign && $old_campaign != '') {
                //
            }
            $results[] = $rec;
            $old_campaign = $rec['Campaign'];
        }

        return $results;
    }

    private function getAgentActivity($campaign, $startDate, $endDate)
    {

        $bind = [
            'group_id' => Auth::user()->group_id,
            'startdate' => $startDate,
            'enddate' => $endDate,
            'campaign' => $campaign,
        ];

        // loop thru results looking for log in/out times
        // total up paused and not paused times
        // then do our sorting
        // finally, format fields

        $tmpsheet = [];

        $oldrep = '';
        $i = 0;
        foreach ($this->yieldSql($sql, $bind) as $rec) {
            if ($rec['Rep'] != $oldrep) {
                $i++;
                $oldrep = $rec['Rep'];
                $loggedin = false;
                $tmpsheet[$i]['Date'] = $rec['Date'];
                $tmpsheet[$i]['Rep'] = $rec['Rep'];
                $tmpsheet[$i]['Campaign'] = '';
                $tmpsheet[$i]['LogInTime'] = '';
                $tmpsheet[$i]['LogOutTime'] = '';
                $tmpsheet[$i]['ManHourSec'] = 0;
                $tmpsheet[$i]['PausedTimeSec'] = 0;
            }
            switch ($rec['Action']) {
                case 'Login':
                    if (!$loggedin) {
                        $tmpsheet[$i]['LogInTime'] = $rec['Date'];
                        $tmpsheet[$i]['Campaign'] = $rec['Campaign'];
                        $loggedin = true;
                    }
                    break;
                case 'Logout':
                    if ($loggedin) {
                        $tmpsheet[$i]['LogOutTime'] = $rec['Date'];
                        $loggedin = false;
                        $oldrep = '';  // force a new record
                    }
                    break;
                case 'Paused':
                    if ($loggedin) {
                        $tmpsheet[$i]['PausedTimeSec'] += $rec['Duration'];
                    }
                    break;
                default:
                    if ($loggedin) {
                        $tmpsheet[$i]['ManHourSec'] += $rec['Duration'];
                    }
            }
        }

        // remove any rows that don't have login and logout times
        $results = [];
        foreach ($tmpsheet as $rec) {
            if ($rec['LogInTime'] != '' || $rec['LogOutTime'] != '') {
                $results[] = $rec;
            }
        }

        // now sort
        if (!empty($this->params['orderby'])) {
            $field = key($this->params['orderby']);
            $dir = $this->params['orderby'][$field] == 'desc' ? SORT_DESC : SORT_ASC;
            $col = array_column($results, $field);
            array_multisort($col, $dir, $results);
        }

        // this sets the order of the columns
        foreach ($this->params['columns'] as $k => $v) {
            $total[$k] = '';
        }

        $total['Date'] = 'Total:';
        $total['ManHourSec'] = 0;
        $total['PausedTimeSec'] = 0;

        foreach ($results as &$rec) {
            $total['ManHourSec'] += $rec['ManHourSec'];
            $total['PausedTimeSec'] += $rec['PausedTimeSec'];

            $rec['Date'] = Carbon::parse($rec['Date'])->format('m/d/Y');
            $rec['LogInTime'] = Carbon::parse($rec['LogInTime'])->isoFormat('L LT');
            $rec['LogOutTime'] = Carbon::parse($rec['LogOutTime'])->isoFormat('L LT');
            $rec['ManHourSec'] = $this->secondsToHms($rec['ManHourSec']);
            $rec['PausedTimeSec'] = $this->secondsToHms($rec['PausedTimeSec']);
        }

        // format totals
        $total['ManHourSec'] = $this->secondsToHms($total['ManHourSec']);
        $total['PausedTimeSec'] = $this->secondsToHms($total['PausedTimeSec']);

        // Tack on the totals row
        $results[] = $total;

        return $results;
    }

    private function processInput(Request $request)
    {
        // Get vals from session if not set (for exports)
        $request = $this->getSessionParams($request);

        // Check page filters
        $this->checkPageFilters($request);

        // Check report filters
        $this->checkDateRangeFilters($request);

        if (empty($request->reps)) {
            $this->errors->add('reps.required', trans('reports.errrepsrequired'));
        } else {
            $this->params['reps'] = $request->reps;
        }

        if (!empty($request->skills)) {
            $this->params['skills'] = $request->skills;
        }

        // Save params to session
        $this->saveSessionParams();

        return $this->errors;
    }
}
