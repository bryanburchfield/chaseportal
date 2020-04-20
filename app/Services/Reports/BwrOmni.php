<?php

namespace App\Services\Reports;

use App\Traits\CampaignTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\ReportTraits;

class BwrOmni
{
    use ReportTraits;
    use CampaignTraits;

    public function __construct()
    {
        $this->initilaizeParams();

        $this->params['reportName'] = 'reports.bwr_omni';
        $this->params['nostreaming'] = 1;
        $this->params['campaigns'] = [];
        $this->params['data_sources_primary'] = [];
        $this->params['data_sources_secondary'] = [];
        $this->params['programs'] = [];
        $this->params['columns'] = [
            'Campaign' => 'reports.campaign',
            'Subcampaign' => 'reports.subcampaign',
            'Data_Source_Primary' => 'reports.data_source_primary',
            'Data_Source_Secondary' => 'reports.data_source_secondary',
            'Program' => 'reports.program',
            'TotalLeads' => 'reports.total_leads',
            'Callable' => 'reports.available',
            'SalesPerAttempt' => 'reports.sales_per_attempt',
            'AvgAttempts' => 'reports.avg_attempts',
            'Dials' => 'reports.dialed',
            'Connects' => 'reports.connects',
            'ConnectRate' => 'reports.connectrate',
            'Contacts' => 'reports.contacts',
            'ContactRate' => 'reports.contactrate',
            'Sales' => 'reports.sales',
            'SalesPerDial' => 'reports.sales_per_dial',
            'ConversionRate' => 'reports.conversionrate',
            'ManHourSec' => 'reports.manhours',
            'DialsPerManHour' => 'reports.dials_per_manhour',
            'ConnectsPerManHour' => 'reports.connects_per_manhour',
            'ContactsPerManHour' => 'reports.contacts_per_manhour',
            'SalesPerManHour' => 'reports.sales_per_manhour',
            'WaitingTimeSec' => 'reports.waittimesec',
            'AvgWaitingTimeSec' => 'reports.avwaittime',
            'CallTimeSec' => 'reports.talktimesec',
            'AvgCallTimeSec' => 'reports.avtalktime',
            'PausedTimeSec' => 'reports.pausedtimesec',
            'DispositionTimeSec' => 'reports.dispositiontimesec',
        ];
    }

    public function getFilters()
    {
        $filters = [
            'campaigns' => $this->getAllCampaigns(
                $this->params['fromdate'],
                $this->params['todate']
            ),
            'data_sources_primary' => $this->getAllDataSourcePrimary(),
            'data_sources_secondary' => $this->getAllDataSourceSecondary(),
            'programs' => $this->getAllProgram(),
            'db_list' => Auth::user()->getDatabaseArray(),
        ];

        return $filters;
    }

    public function getInfo()
    {
        return [
            'columns' => $this->params['columns'],
            'paragraphs' => 2,
        ];
    }

    private function getAllDataSourcePrimary()
    {
        $db = Auth::User()->db;

        $sql = '';

        $sql .=  "SELECT DISTINCT Data_Source_Primary
            FROM [$db].[dbo].[ADVANCED_BWR_Master_Table]
            WHERE Data_Source_Primary is not null
            AND Data_Source_Primary != ''";

        $results = resultsToList($this->runSql($sql));

        ksort($results, SORT_NATURAL | SORT_FLAG_CASE);

        return $results;
    }

    private function getAllDataSourceSecondary()
    {
        $db = Auth::User()->db;

        $sql = '';

        $sql .=  "SELECT DISTINCT Data_Source_Secondary
            FROM [$db].[dbo].[ADVANCED_BWR_Master_Table]
            WHERE Data_Source_Secondary is not null
            AND Data_Source_Secondary != ''";

        $results = resultsToList($this->runSql($sql));

        ksort($results, SORT_NATURAL | SORT_FLAG_CASE);

        return $results;
    }

    private function getAllProgram()
    {
        $db = Auth::User()->db;

        $sql = '';

        $sql .=  "SELECT DISTINCT Program
            FROM [$db].[dbo].[ADVANCED_BWR_Master_Table]
            WHERE Program is not null
            AND Program != ''";

        $results = resultsToList($this->runSql($sql));

        ksort($results, SORT_NATURAL | SORT_FLAG_CASE);

        return $results;
    }

    private function executeReport($all = false)
    {
        $this->setHeadings();

        list($fromDate, $toDate) = $this->dateRange($this->params['fromdate'], $this->params['todate']);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $campaigns = str_replace("'", "''", implode('!#!', $this->params['campaigns']));

        $bind = [
            'group_id1' => Auth::user()->group_id,
            'group_id2' => Auth::user()->group_id,
            'startdate' => $startDate,
            'enddate' => $endDate,
            'campaigns' => $campaigns,
        ];

        $sql = "SET NOCOUNT ON;";

        if (!empty($this->params['data_sources_primary'])) {
            $data_sources_primary = str_replace("'", "''", implode('!#!', $this->params['data_sources_primary']));
            $bind['data_sources_primary'] = $data_sources_primary;

            $sql .= "
            CREATE TABLE #SelectedPrimary(Data_Source_Primary varchar(255) Primary Key);
            INSERT INTO #SelectedPrimary SELECT DISTINCT [value] from dbo.SPLIT(:data_sources_primary, '!#!');";
        }

        if (!empty($this->params['data_sources_secondary'])) {
            $data_sources_secondary = str_replace("'", "''", implode('!#!', $this->params['data_sources_secondary']));
            $bind['data_sources_secondary'] = $data_sources_secondary;

            $sql .= "
            CREATE TABLE #SelectedSecondary(Data_Source_Secondary varchar(255) Primary Key);
            INSERT INTO #SelectedSecondary SELECT DISTINCT [value] from dbo.SPLIT(:data_sources_secondary, '!#!');";
        }

        if (!empty($this->params['programs'])) {
            $programs = str_replace("'", "''", implode('!#!', $this->params['programs']));
            $bind['programs'] = $programs;

            $sql .= "
            CREATE TABLE #SelectedProgram(Program varchar(255) Primary Key);
            INSERT INTO #SelectedProgram SELECT DISTINCT [value] from dbo.SPLIT(:programs, '!#!');";
        }

        $sql .= "
            CREATE TABLE #SelectedCampaign(CampaignName varchar(50) Primary Key);
            INSERT INTO #SelectedCampaign SELECT DISTINCT [value] from dbo.SPLIT(:campaigns, '!#!');
            
            SELECT
                Campaign,
                Subcampaign,
                Data_Source_Primary,
                Data_Source_Secondary,
                Program,
                SUM(Attempt) as Attempts,
                COUNT(*) as TotalLeads,
                SUM(CAST(IsCallable as INT)) As Callable,
                CASE
                    WHEN SUM(Attempt) = 0 THEN 0
                    ELSE SUM(CASE WHEN Type = 3 THEN 1 ELSE 0 END) / SUM(CAST(Attempt as decimal))
                END as SalesPerAttempt,
                AVG(CAST(Attempt as decimal)) as AvgAttempts
            INTO #tmp_leads
            FROM (
                SELECT
                    L.Campaign,
                    A.Data_Source_Primary,
                    A.Data_Source_Secondary,
                    A.Program,
                    IsNull(L.Subcampaign, '') as Subcampaign,
                    L.Attempt,
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
                INNER JOIN #SelectedCampaign C on C.CampaignName = L.Campaign
                LEFT OUTER JOIN Dispos D ON D.Disposition = L.CallStatus AND D.GroupId = L.GroupId
                INNER JOIN ADVANCED_BWR_Master_Table A ON A.LeadID = L.IdGuid";

        if (
            !empty($this->params['data_sources_primary']) ||
            !empty($this->params['data_sources_secondary']) ||
            !empty($this->params['programs'])
        ) {
            if (!empty($this->params['data_sources_primary'])) {
                $sql .= "
                INNER JOIN #SelectedPrimary SP on SP.Data_Source_Primary = A.Data_Source_Primary";
            }
            if (!empty($this->params['data_sources_secondary'])) {
                $sql .= "
                INNER JOIN #SelectedSecondary SS on SS.Data_Source_Secondary = A.Data_Source_Secondary";
            }
            if (!empty($this->params['programs'])) {
                $sql .= "
                INNER JOIN #SelectedProgram PP on PP.Program = A.Program";
            }
        }

        $sql .= "
                WHERE L.GroupId = :group_id1
            ) tmp
            GROUP BY Campaign, Subcampaign, Data_Source_Primary, Data_Source_Secondary, Program;

            SELECT
                Campaign,
                Subcampaign,
                Data_Source_Primary,
                Data_Source_Secondary,
                Program,
                COUNT(*) as Dials,
                SUM(CASE WHEN Type > 0 THEN 1 ELSE 0 END) as Connects,
        CASE 
            WHEN COUNT(*) = 0 THEN 0
            ELSE SUM(CASE WHEN Type > 0 THEN 1 ELSE 0 END) / CAST(COUNT(*) as decimal)
        END as ConnectRate,
                SUM(CASE WHEN Type > 1 THEN 1 ELSE 0 END) as Contacts,
        CASE 
            WHEN COUNT(*) = 0 THEN 0
            ELSE SUM(CASE WHEN Type > 1 THEN 1 ELSE 0 END) / CAST(COUNT(*) as decimal)
        END as ContactRate,
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
                    A.Data_Source_Primary,
                    A.Data_Source_Secondary,
                    A.Program,
                    DR.Attempt,
                    D.Type
                FROM DialingResults DR
                INNER JOIN #SelectedCampaign C on C.CampaignName = DR.Campaign
                LEFT OUTER JOIN Dispos D ON D.Disposition = DR.CallStatus AND D.GroupId = DR.GroupId
                INNER JOIN Leads L ON L.id = DR.LeadId 
                INNER JOIN ADVANCED_BWR_Master_Table A ON A.LeadID = L.IdGuid";

        if (
            !empty($this->params['data_sources_primary']) ||
            !empty($this->params['data_sources_secondary']) ||
            !empty($this->params['programs'])
        ) {

            if (!empty($this->params['data_sources_primary'])) {
                $sql .= "
                    INNER JOIN #SelectedPrimary SP on SP.Data_Source_Primary = A.Data_Source_Primary";
            }
            if (!empty($this->params['data_sources_secondary'])) {
                $sql .= "
                    INNER JOIN #SelectedSecondary SS on SS.Data_Source_Secondary = A.Data_Source_Secondary";
            }
            if (!empty($this->params['programs'])) {
                $sql .= "
                    INNER JOIN #SelectedProgram PP on PP.Program = A.Program";
            }
        }

        $sql .= "
                WHERE DR.GroupId = :group_id2
                AND DR.Date >= :startdate
                AND DR.Date < :enddate
            ) tmp
            GROUP BY Campaign, Subcampaign, Data_Source_Primary, Data_Source_Secondary, Program;

            SELECT
                L.*,
                C.Dials,
                C.Connects,
                C.ConnectRate,
                C.Contacts,
                C.ContactRate,
                C.Sales,
                C.SalesPerDial,
                C.ConversionRate 
            FROM #tmp_leads L
            LEFT OUTER JOIN #tmp_calls C ON
                C.Campaign = L.Campaign AND
                C.Subcampaign = L.Subcampaign AND
                C.Data_Source_Primary = L.Data_Source_Primary AND
                C.Data_Source_Secondary = L.Data_Source_Secondary AND
                C.Program = L.Program
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

        $totals = [
            'TotalLeads' => 0,
            'Attempts' => 0,
            'Callable' => 0,
            'Dials' => 0,
            'Connects' => 0,
            'Contacts' => 0,
            'Sales' => 0,
        ];

        $results = [];
        $old_campaign = '';

        foreach ($this->yieldSql($sql, $bind) as $rec) {
            if (empty($rec)) {
                continue;
            }

            if ($rec['Campaign'] != $old_campaign && $old_campaign != '') {
                // append agent actiivity to results
                $results[] = $this->addCampaignTotals($totals, $old_campaign, $bind['startdate'], $bind['enddate']);

                // insert a blank line
                $results[] = $this->emptyBaseRec() + $this->emptyActivityRec();

                // clear totals
                $totals = [
                    'TotalLeads' => 0,
                    'Attempts' => 0,
                    'Callable' => 0,
                    'Dials' => 0,
                    'Connects' => 0,
                    'Contacts' => 0,
                    'Sales' => 0,
                ];
            }
            $old_campaign = $rec['Campaign'];

            // add to totals
            $totals['TotalLeads'] += $rec['TotalLeads'];
            $totals['Attempts'] += $rec['Attempts'];
            $totals['Callable'] += $rec['Callable'];
            $totals['Dials'] += $rec['Dials'];
            $totals['Connects'] += $rec['Connects'];
            $totals['Contacts'] += $rec['Contacts'];
            $totals['Sales'] += $rec['Sales'];

            // unset Attempts since we aren't priting it
            unset($rec['Attempts']);

            // Add empty activity fields to rec
            $rec = $rec + $this->emptyActivityRec();

            // if outer join didn't find any dials, fill with 0s
            if ($rec['Dials'] == '') {
                $rec['Dials'] = 0;
                $rec['Connects'] = 0;
                $rec['ConnectRate'] = 0;
                $rec['Contacts'] = 0;
                $rec['ContactRate'] = 0;
                $rec['Sales'] = 0;
                $rec['SalesPerDial'] = 0;
                $rec['ConversionRate'] = 0;
            }

            // format fields
            $rec = $this->formatFields($rec);

            $results[] = $rec;
        }

        // add last campaign actiivity
        if (!empty($old_campaign)) {
            $results[] = $this->addCampaignTotals($totals, $old_campaign, $bind['startdate'], $bind['enddate']);
        }

        return $results;
    }

    private function addCampaignTotals($totals, $campaign, $startDate, $endDate)
    {
        // build empty record
        $rec = $this->emptyBaseRec() + $this->emptyActivityRec();

        $rec['Campaign'] = strtoupper(trans('reports.total')) . ':';
        $rec['Subcampaign'] = '';

        // calculate totals
        $rec['TotalLeads'] = $totals['TotalLeads'];
        $rec['Callable'] = $totals['Callable'];
        $rec['Dials'] = $totals['Dials'];
        $rec['Connects'] = $totals['Connects'];
        $rec['Contacts'] = $totals['Contacts'];
        $rec['Sales'] = $totals['Sales'];

        if ($totals['Attempts'] == 0) {
            $rec['SalesPerAttempt'] = 0;
        } else {
            $rec['SalesPerAttempt'] = $totals['Sales'] / $totals['Attempts'];
        }

        if ($totals['TotalLeads'] == 0) {
            $rec['AvgAttempts'] = 0;
        } else {
            $rec['AvgAttempts'] = $totals['Attempts'] / $totals['TotalLeads'];
        }

        if ($totals['Dials'] == 0) {
            $rec['ConnectRate'] = 0;
            $rec['ContactRate'] = 0;
            $rec['SalesPerDial'] = 0;
        } else {
            $rec['ConnectRate'] = $totals['Connects'] / $totals['Dials'];
            $rec['ContactRate'] = $totals['Contacts'] / $totals['Dials'];
            $rec['SalesPerDial'] = $totals['Sales'] / $totals['Dials'];
        }

        if ($totals['Contacts'] == 0) {
            $rec['ConversionRate'] = 0;
        } else {
            $rec['ConversionRate'] = $totals['Sales'] / $totals['Contacts'];
        }

        // format fields
        $rec = $this->formatFields($rec);

        // Get agent activity
        $activity = $this->getAgentActivity($campaign, $startDate, $endDate);

        $rec['ManHourSec'] = $activity['ManHourSec'];
        $rec['WaitingTimeSec'] = $activity['WaitingTimeSec'];
        $rec['CallTimeSec'] = $activity['CallTimeSec'];
        $rec['PausedTimeSec'] = $activity['PausedTimeSec'];
        $rec['DispositionTimeSec'] = $activity['DispositionTimeSec'];
        $rec['AvgWaitingTimeSec'] = $activity['AvgWaitingTimeSec'];
        $rec['AvgCallTimeSec'] = $activity['AvgCallTimeSec'];

        if ($rec['ManHourSec'] == 0) {
            $rec['DialsPerManHour'] = 0;
            $rec['ConnectsPerManHour'] = 0;
            $rec['ContactsPerManHour'] = 0;
            $rec['SalesPerManHour'] = 0;
        } else {
            $rec['DialsPerManHour'] = $totals['Dials'] / ($rec['ManHourSec'] / 3600);
            $rec['ConnectsPerManHour'] = $totals['Connects'] / ($rec['ManHourSec'] / 3600);
            $rec['ContactsPerManHour'] = $totals['Contacts'] / ($rec['ManHourSec'] / 3600);
            $rec['SalesPerManHour'] = $totals['Sales'] / ($rec['ManHourSec'] / 3600);
        }

        // format fields
        $rec['ManHourSec'] = $this->secondsToHms($rec['ManHourSec']);
        $rec['DialsPerManHour'] = number_format($rec['DialsPerManHour'], 2);
        $rec['ConnectsPerManHour'] = number_format($rec['ConnectsPerManHour'], 2);
        $rec['ContactsPerManHour'] = number_format($rec['ContactsPerManHour'], 2);
        $rec['SalesPerManHour'] = number_format($rec['SalesPerManHour'], 2);
        $rec['WaitingTimeSec'] = $this->secondsToHms($rec['WaitingTimeSec']);
        $rec['AvgWaitingTimeSec'] = $this->secondsToHms($rec['AvgWaitingTimeSec']);
        $rec['CallTimeSec'] = $this->secondsToHms($rec['CallTimeSec']);
        $rec['AvgCallTimeSec'] = $this->secondsToHms($rec['AvgCallTimeSec']);
        $rec['PausedTimeSec'] = $this->secondsToHms($rec['PausedTimeSec']);
        $rec['DispositionTimeSec'] = $this->secondsToHms($rec['DispositionTimeSec']);

        return $rec;
    }

    private function formatFields($rec)
    {
        $rec['SalesPerAttempt'] = number_format($rec['SalesPerAttempt'], 4);
        $rec['AvgAttempts'] = number_format($rec['AvgAttempts'], 2);
        $rec['ConnectRate'] = number_format($rec['ConnectRate'] * 100, 2) . '%';
        $rec['ContactRate'] = number_format($rec['ContactRate'] * 100, 2) . '%';
        $rec['SalesPerDial'] = number_format($rec['SalesPerDial'], 4);
        $rec['ConversionRate'] = number_format($rec['ConversionRate'] * 100, 2) . '%';

        return $rec;
    }

    private function getAgentActivity($campaign, $startDate, $endDate)
    {
        $bind = [
            'group_id' => Auth::user()->group_id,
            'startdate' => $startDate,
            'enddate' => $endDate,
            'campaign' => $campaign,
        ];

        $sql = "SELECT Rep, Date, Action, Duration
                FROM AgentActivity
                WHERE GroupId = :group_id
                AND Date >= :startdate
                AND Date < :enddate
                and Campaign = :campaign
                ORDER BY Rep, Date";

        $results = $this->processActivity($sql, $bind);

        return $results;
    }

    private function processActivity($sql, $bind)
    {
        // loop thru results looking for log in/out times
        // total up paused and not paused times
        // then do our sorting
        // finally, format fields

        $tmpsheet = [];

        $waits = 0;
        $calls = 0;

        $oldrep = '';
        $i = 0;
        foreach ($this->yieldSql($sql, $bind) as $rec) {
            if ($rec['Rep'] != $oldrep) {
                $i++;
                $oldrep = $rec['Rep'];
                $loggedin = false;
                $tmpsheet[$i]['Date'] = $rec['Date'];
                $tmpsheet[$i]['Rep'] = $rec['Rep'];
                $tmpsheet[$i]['LogInTime'] = '';
                $tmpsheet[$i]['LogOutTime'] = '';
                $tmpsheet[$i]['ManHourSec'] = 0;
                $tmpsheet[$i]['PausedTimeSec'] = 0;
                $tmpsheet[$i]['WaitingTimeSec'] = 0;
                $tmpsheet[$i]['CallTimeSec'] = 0;
                $tmpsheet[$i]['DispositionTimeSec'] = 0;
            }
            switch ($rec['Action']) {
                case 'Login':
                    if (!$loggedin) {
                        $tmpsheet[$i]['LogInTime'] = $rec['Date'];
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
                case 'Waiting':
                    if ($loggedin) {
                        $waits++;
                        $tmpsheet[$i]['WaitingTimeSec'] += $rec['Duration'];
                        $tmpsheet[$i]['ManHourSec'] += $rec['Duration'];
                    }
                    break;
                case 'Call':
                    if ($loggedin) {
                        $calls++;
                        $tmpsheet[$i]['CallTimeSec'] += $rec['Duration'];
                        $tmpsheet[$i]['ManHourSec'] += $rec['Duration'];
                    }
                    break;
                case 'Disposition':
                    if ($loggedin) {
                        $tmpsheet[$i]['DispositionTimeSec'] += $rec['Duration'];
                        $tmpsheet[$i]['ManHourSec'] += $rec['Duration'];
                    }
                    break;
                default:
                    if ($loggedin) {
                        $tmpsheet[$i]['ManHourSec'] += $rec['Duration'];
                    }
            }
        }

        $results = [
            'ManHourSec' => 0,
            'PausedTimeSec' => 0,
            'WaitingTimeSec' => 0,
            'CallTimeSec' => 0,
            'DispositionTimeSec' => 0,
            'AvgWaitingTimeSec' => 0,
            'AvgCallTimeSec' => 0,
        ];

        // remove any rows that don't have login and logout times
        foreach ($tmpsheet as $rec) {
            if ($rec['LogInTime'] != '' || $rec['LogOutTime'] != '') {
                $results['ManHourSec'] += $rec['ManHourSec'];
                $results['PausedTimeSec'] += $rec['PausedTimeSec'];
                $results['WaitingTimeSec'] += $rec['WaitingTimeSec'];
                $results['CallTimeSec'] += $rec['ManHourSec'];
                $results['DispositionTimeSec'] += $rec['ManHourSec'];
            }
        }

        if ($waits > 0) {
            $results['AvgWaitingTimeSec'] = $results['WaitingTimeSec'] / $waits;
        }
        if ($calls > 0) {
            $results['AvgCallTimeSec'] = $results['WaitingTimeSec'] / $calls;
        }

        return $results;
    }

    private function emptyBaseRec()
    {
        return [
            'Campaign' => '',
            'Subcampaign' => '',
            'Data_Source_Primary' => '',
            'Data_Source_Secondary' => '',
            'Program' => '',
            'TotalLeads' => '',
            'Callable' => '',
            'SalesPerAttempt' => '',
            'AvgAttempts' => '',
            'Dials' => '',
            'Connects' => '',
            'ConnectRate' => '',
            'Contacts' => '',
            'ContactRate' => '',
            'Sales' => '',
            'SalesPerDial' => '',
            'ConversionRate' => '',
        ];
    }

    private function emptyActivityRec()
    {
        return [
            'ManHourSec' => '',
            'DialsPerManHour' => '',
            'ConnectsPerManHour' => '',
            'ContactsPerManHour' => '',
            'SalesPerManHour' => '',
            'WaitingTimeSec' => '',
            'AvgWaitingTimeSec' => '',
            'CallTimeSec' => '',
            'AvgCallTimeSec' => '',
            'PausedTimeSec' => '',
            'DispositionTimeSec' => '',
        ];
    }

    private function processInput(Request $request)
    {
        // Get vals from session if not set (for exports)
        $request = $this->getSessionParams($request);

        // Check page filters
        $this->checkPageFilters($request);

        // Check report filters
        $this->checkDateRangeFilters($request);

        if (empty($request->campaigns)) {
            $this->errors->add('campaign.required', trans('reports.errcampaignrequired'));
        } else {
            $this->params['campaigns'] = $request->campaigns;
        }
        if (!empty($request->data_sources_primary)) {
            $this->params['data_sources_primary'] = $request->data_sources_primary;
        }

        if (!empty($request->data_sources_secondary)) {
            $this->params['data_sources_secondary'] = $request->data_sources_secondary;
        }

        if (!empty($request->programs)) {
            $this->params['programs'] = $request->programs;
        }

        // Save params to session
        $this->saveSessionParams();

        return $this->errors;
    }
}
