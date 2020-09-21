<?php

namespace App\Services\Reports;

use App\Traits\BwrTraits;
use App\Traits\CampaignTraits;
use App\Traits\ReportTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BwrProductionReport
{
    use BwrTraits;
    use CampaignTraits;
    use ReportTraits;

    public function __construct()
    {
        $this->initilaizeParams();

        $this->params['reportName'] = 'reports.production_report';
        $this->params['nostreaming'] = 1;
        $this->params['campaigns'] = [];
        $this->params['data_sources_primary'] = [];
        $this->params['data_sources_secondary'] = [];
        $this->params['programs'] = [];
        $this->params['skills'] = [];
        $this->params['hasTotals'] = true;
        $this->params['columns'] = [];  // columns are mostly dynamic
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
            'skills' => $this->getAllSkills(),
            'db_list' => Auth::user()->getDatabaseArray(),
        ];

        return $filters;
    }

    public function getInfo()
    {
        // Columns are mostly dynamic, so here are the static ones
        $columns = [
            'Rep' => 'reports.rep',
            'ManHours' => 'reports.manhours',
            'Connects' => 'reports.connects',
            'Contacts' => 'reports.contacts',
            'ContsPerHour' => 'reports.contacts_per_manhour',
            'Sales' => 'reports.sales',
            'SalesHr' => 'reports.sales_per_manhour',
        ];

        return [
            'columns' => $columns,
            'paragraphs' => 2,
        ];
    }

    private function executeReport($all = false)
    {
        $this->setHeadings();

        list($fromDate, $toDate) = $this->dateRange($this->params['fromdate'], $this->params['todate']);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');
        $campaigns = str_replace("'", "''", implode('!#!', $this->params['campaigns']));
        $skills = str_replace("'", "''", implode('!#!', $this->params['skills']));

        // Reps and CallStatuses tables
        $reps = [];
        $stats = [];

        // Get Rep hours worked
        $sql = "SET NOCOUNT ON;";

        if (!empty($this->params['skills'])) {
            $sql .= "
            CREATE TABLE #SelectedSkill(SkillName varchar(50) Primary Key);
            INSERT INTO #SelectedSkill SELECT DISTINCT [value] from dbo.SPLIT('$skills', '!#!');";
        }

        $sql .= "
        SELECT Rep, SUM(Duration) ManHours FROM (";

        $bind = [];
        $union = '';
        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id' . $i] =  Auth::user()->group_id;
            $bind['startdate' . $i] = $startDate;
            $bind['enddate' . $i] = $endDate;

            $sql .= " $union SELECT Rep, Duration
            FROM [$db].[dbo].[AgentActivity]";

            if (!empty($this->params['skills'])) {
                $sql .= "
                INNER JOIN [$db].[dbo].[Reps] RR on RR.RepName COLLATE SQL_Latin1_General_CP1_CS_AS = AA.Rep
                INNER JOIN #SelectedSkill SS on SS.SkillName COLLATE SQL_Latin1_General_CP1_CS_AS = RR.Skill";
            }

            $sql .= "
            WHERE GroupId = :group_id$i
            AND date >= :startdate$i
            AND date < :enddate$i
            AND [Action] NOT IN ('Paused','Login','Logout')";

            if (!empty($campaigns)) {
                $bind['campaigns' . $i] = $campaigns;
                $sql .= " AND Campaign in (SELECT value FROM dbo.SPLIT(:campaigns$i, '!#!'))";
            }

            if (session('ssoRelativeCampaigns', 0)) {
                $sql .= " AND Campaign IN (SELECT CampaignName FROM dbo.GetAllRelativeCampaigns(:ssousercamp1$i, 1))";
                $bind['ssousercamp1' . $i] = session('ssoUsername');
            }

            if (session('ssoRelativeReps', 0)) {
                $sql .= " AND Rep IN (SELECT RepName FROM dbo.GetAllRelativeReps(:ssouserrep1$i))";
                $bind['ssouserrep1' . $i] = session('ssoUsername');
            }

            $union = 'UNION ALL';
        }

        $sql .= ") tmp
            GROUP BY Rep";

        $results = $this->runSql($sql, $bind);

        foreach ($results as $rec) {
            $reps[$rec['Rep']] = [
                'Rep' => $rec['Rep'],
                'ManHours' => $rec['ManHours'],
                'Stats' => [],
                'Connects' => 0,
                'Contacts' => 0,
                'ContsPerHour' => 0,
                'Sales' => 0,
                'APH' => 0,
            ];
        }

        // Now get dialing results
        $bind = [];

        $sql = 'SET NOCOUNT ON;';

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

        if (!empty($this->params['skills'])) {
            $sql .= "
            CREATE TABLE #SelectedSkill(SkillName varchar(50) Primary Key);
            INSERT INTO #SelectedSkill SELECT DISTINCT [value] from dbo.SPLIT('$skills', '!#!');";
        }

        $sql .= "
        SELECT Rep, CallStatus,
            'Calls' = SUM(Calls),
            'Connects' = SUM(Connects),
            'Contacts' = SUM(Contacts),
            'Sales' = SUM(Sales)
        FROM (
        ";

        $union = '';
        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id' . $i] =  Auth::user()->group_id;
            $bind['startdate' . $i] = $startDate;
            $bind['enddate' . $i] = $endDate;

            $sql .= " $union SELECT DR.Rep, DR.CallStatus,
            'Calls' = 1,
            'Connects' = CASE WHEN DI.Type > 0 THEN 1 ELSE 0 END,
            'Contacts' = CASE WHEN DI.Type > 1 THEN 1 ELSE 0 END,
            'Sales' = CASE WHEN DI.Type = 3 THEN 1 ELSE 0 END
            FROM [$db].[dbo].[DialingResults] DR
            INNER JOIN [$db].[dbo].[Dispos] DI ON DI.id = DR.DispositionId
            INNER JOIN [$db].[dbo].[Leads] L ON L.id = DR.LeadId 
            INNER JOIN [$db].[dbo].[ADVANCED_BWR_Master_Table] A ON A.LeadID = L.IdGuid";

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

            if (!empty($this->params['skills'])) {
                $sql .= "
                INNER JOIN [$db].[dbo].[Reps] RR on RR.RepName COLLATE SQL_Latin1_General_CP1_CS_AS = DR.Rep
                INNER JOIN #SelectedSkill SS on SS.SkillName COLLATE SQL_Latin1_General_CP1_CS_AS = RR.Skill";
            }

            $sql .= "
            WHERE DR.GroupId = :group_id$i
            AND DR.Date >= :startdate$i
            AND DR.Date < :enddate$i
            AND DR.Rep != ''
            AND DR.CallStatus NOT LIKE 'CR[_]%'
            AND DR.CallStatus NOT IN ('','Inbound','Inbound Voicemail','SMS Delivered','SMS Received')";

            if (!empty($campaigns)) {
                $bind['campaigns1' . $i] = $campaigns;
                $sql .= " AND DR.Campaign in (SELECT value FROM dbo.SPLIT(:campaigns1$i, '!#!'))";
            }

            if (session('ssoRelativeCampaigns', 0)) {
                $sql .= " AND DR.Campaign IN (SELECT CampaignName FROM dbo.GetAllRelativeCampaigns(:ssousercamp2$i, 1))";
                $bind['ssousercamp2' . $i] = session('ssoUsername');
            }

            if (session('ssoRelativeReps', 0)) {
                $sql .= " AND DR.Rep IN (SELECT RepName FROM dbo.GetAllRelativeReps(:ssouserrep2$i))";
                $bind['ssouserrep2' . $i] = session('ssoUsername');
            }

            $union = 'UNION ALL';
        }

        $sql .= ") tmp
        GROUP BY Rep, CallStatus";

        foreach ($this->yieldSql($sql, $bind) as $rec) {
            if (!count($rec)) {
                break;
            }

            // save to stats table
            $stats[$rec['CallStatus']] = $rec['CallStatus'];

            // see if we have this rep
            if (!isset($reps[$rec['Rep']])) {
                $reps[$rec['Rep']] = [
                    'Rep' => $rec['Rep'],
                    'ManHours' => 0,
                    'Stats' => [],
                    'Connects' => 0,
                    'Contacts' => 0,
                    'ContsPerHour' => 0,
                    'Sales' => 0,
                    'APH' => 0,
                ];
            }

            $reps[$rec['Rep']]['Connects'] += $rec['Connects'];
            $reps[$rec['Rep']]['Contacts'] += $rec['Contacts'];
            $reps[$rec['Rep']]['Sales'] += $rec['Sales'];

            // save rep stats
            if (!isset($reps[$rec['Rep']]['Stats'][$rec['CallStatus']])) {
                $reps[$rec['Rep']]['Stats'][$rec['CallStatus']] = 0;
            }
            $reps[$rec['Rep']]['Stats'][$rec['CallStatus']] += $rec['Calls'];
        }

        // sort reps and stats
        ksort($reps, SORT_STRING | SORT_FLAG_CASE);
        ksort($stats, SORT_STRING | SORT_FLAG_CASE);

        $results = $this->processResults($reps, $stats);

        if (empty($results)) {
            $this->params['totrows'] = 0;
            $this->params['totpages'] = 1;
            $this->params['curpage'] = 1;
        } else {
            $this->params['totrows'] = count($results);
            $this->params['totpages'] = floor($this->params['totrows'] / $this->params['pagesize']);
            $this->params['totpages'] += floor($this->params['totrows'] / $this->params['pagesize']) == ($this->params['totrows'] / $this->params['pagesize']) ? 0 : 1;
        }

        return $this->getPage($results, $all);
    }

    private function processResults($reps, $stats)
    {
        $results = [];

        if (!count($reps)) {
            return $results;
        }

        // start to set up blank row
        $zerorec = [
            'Rep' => '',
            'ManHours' => 0,
        ];

        // Columns are variable, so set them now
        $this->params['columns'] = [
            'Rep' => trans('reports.rep'),
            'ManHours' => trans('reports.manhours'),
        ];

        foreach ($stats as $call_status) {
            $this->params['columns'][] = $call_status;
            $zerorec[$call_status] = 0;
        }

        $this->params['columns'][] = trans('reports.connects');
        $this->params['columns'][] = trans('reports.contacts');
        $this->params['columns'][] = trans('reports.contacts_per_manhour');
        $this->params['columns'][] = trans('reports.sales');
        $this->params['columns'][] = trans('reports.sales_per_manhour');

        // Finish blank record
        $zerorec['Connects'] = 0;
        $zerorec['Contacts'] = 0;
        $zerorec['ContsPerHour'] = 0;
        $zerorec['Sales'] = 0;
        $zerorec['APH'] = 0;

        // Create totals record
        $total = $zerorec;
        $total['Rep'] = trans('reports.total') . ':';

        foreach ($reps as $rep => $reprec) {
            $row = $zerorec;

            $row['Rep'] = $rep;
            $row['ManHours'] = $reprec['ManHours'];
            $row['Connects'] = $reprec['Connects'];
            $row['Contacts'] = $reprec['Contacts'];
            $row['Sales'] = $reprec['Sales'];

            // Add to totals
            $total['ManHours'] += $reprec['ManHours'];
            $total['Connects'] += $reprec['Connects'];
            $total['Contacts'] += $reprec['Contacts'];
            $total['Sales'] += $reprec['Sales'];

            foreach ($reprec['Stats'] as $call_status => $count) {
                $row[$call_status] = $count;
                $total[$call_status] += $count;
            }

            // Do calcs
            $row['ManHours'] = number_format($row['ManHours'] / 60 / 60, 2);

            if ($row['ManHours'] == 0) {
                $row['ContsPerHour'] = 0;
                $row['APH'] = 0;
            } else {
                $row['ContsPerHour'] = round($row['Contacts'] / $row['ManHours'], 2);
                $row['APH'] = round($row['Sales'] / $row['ManHours'], 2);
            }

            $results[] = $row;
        }

        // Do calcs
        $total['ManHours'] = number_format($total['ManHours'] / 60 / 60, 2);

        if ($total['ManHours'] == 0) {
            $total['ContsPerHour'] = 0;
            $total['APH'] = 0;
        } else {
            $total['ContsPerHour'] = round($total['Contacts'] / $total['ManHours'], 2);
            $total['APH'] = round($total['Sales'] / $total['ManHours'], 2);
        }

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

        if (!empty($request->campaigns)) {
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

        if (!empty($request->skills)) {
            $this->params['skills'] = $request->skills;
        }

        // Save params to session
        $this->saveSessionParams();

        return $this->errors;
    }
}
