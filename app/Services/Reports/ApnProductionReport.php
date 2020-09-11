<?php

namespace App\Services\Reports;

use App\Traits\CampaignTraits;
use App\Traits\ReportTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ApnProductionReport
{
    use ReportTraits;
    use CampaignTraits;

    public function __construct()
    {
        $this->initilaizeParams();

        $this->params['reportName'] = 'reports.production_report';
        $this->params['nostreaming'] = 1;
        $this->params['campaigns'] = [];
        $this->params['skills'] = [];
        $this->params['threshold_secs'] = 0;
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
            'skills' => $this->getAllSkills(),
            'threshold_secs' => 300,
            'db_list' => Auth::user()->getDatabaseArray(),
        ];

        return $filters;
    }

    public function getInfo()
    {
        // Columns are mostly dynamic, so here are the static ones
        $columns = [
            'Rep' => 'reports.rep',
            'Skill' => 'reports.skill',
            'ManHours' => 'reports.manhours',
            'LoggedInTime' => 'reports.loggedintime',
            'Connects' => 'reports.connects',
            'Contacts' => 'reports.contacts',
            'ContsPerHour' => 'reports.contacts_per_manhour',
            'Sales' => 'reports.sales',
            'SalesHr' => 'reports.sales_per_manhour',
            'ThresholdCalls' => 'reports.threshold_calls',
            'ThresholdRatio' => 'reports.threshold_ratio',
            'ThresholdClosingPct' => 'reports.threshold_closing_pct',
        ];

        return [
            'columns' => $columns,
            'paragraphs' => 2,
        ];
    }

    private function executeReport($all = false)
    {
        Log::debug('exe');

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
        SELECT Rep, Skill, SUM(ManHourSecs) ManHours, SUM(LoggedInSecs) LoggedInTime FROM (";

        $bind = [];
        $union = '';
        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id' . $i] =  Auth::user()->group_id;
            $bind['startdate' . $i] = $startDate;
            $bind['enddate' . $i] = $endDate;

            $sql .= " $union SELECT AA.Rep, RR.Skill,
            AA.Duration as LoggedInSecs,
            CASE WHEN [Action] NOT IN ('Paused','Login','Logout') THEN AA.Duration ELSE 0
            END as ManHourSecs 
            FROM [$db].[dbo].[AgentActivity] AA
            LEFT JOIN [$db].[dbo].[Reps] RR on RR.RepName COLLATE SQL_Latin1_General_CP1_CS_AS = AA.Rep";

            if (!empty($this->params['skills'])) {
                $sql .= "
                INNER JOIN #SelectedSkill SS on SS.SkillName COLLATE SQL_Latin1_General_CP1_CS_AS = RR.Skill";
            }

            $sql .= "
            WHERE AA.GroupId = :group_id$i
            AND AA.date >= :startdate$i
            AND AA.date < :enddate$i";

            if (!empty($campaigns)) {
                $bind['campaigns' . $i] = $campaigns;
                $sql .= " AND AA.Campaign in (SELECT value FROM dbo.SPLIT(:campaigns$i, '!#!'))";
            }

            if (session('ssoRelativeCampaigns', 0)) {
                $sql .= " AND AA.Campaign IN (SELECT CampaignName FROM dbo.GetAllRelativeCampaigns(:ssousercamp1$i, 1))";
                $bind['ssousercamp1' . $i] = session('ssoUsername');
            }

            if (session('ssoRelativeReps', 0)) {
                $sql .= " AND AA.Rep IN (SELECT RepName FROM dbo.GetAllRelativeReps(:ssouserrep1$i))";
                $bind['ssouserrep1' . $i] = session('ssoUsername');
            }

            $union = 'UNION ALL';
        }

        $sql .= ") tmp
            GROUP BY Rep, Skill";

        $results = $this->runSql($sql, $bind);

        foreach ($results as $rec) {
            $reps[$rec['Rep']] = [
                'Rep' => $rec['Rep'],
                'Skill' => $rec['Skill'],
                'ManHours' => $rec['ManHours'],
                'LoggedInTime' => $rec['LoggedInTime'],
                'Stats' => [],
                'Connects' => 0,
                'Contacts' => 0,
                'ContsPerHour' => 0,
                'Sales' => 0,
                'APH' => 0,
                'Calls' => 0,
                'ThresholdSales' => 0,
                'ThresholdCalls' => 0,
                'ThresholdRatio' => 0,
                'ThresholdClosingPct' => 0,
            ];
        }

        // Now get dialing results
        $bind = [];

        $sql = 'SET NOCOUNT ON;';

        if (!empty($this->params['skills'])) {
            $sql .= "
            CREATE TABLE #SelectedSkill(SkillName varchar(50) Primary Key);
            INSERT INTO #SelectedSkill SELECT DISTINCT [value] from dbo.SPLIT('$skills', '!#!');";
        }

        $sql .= "
        SELECT Rep, Skill, CallStatus,
            'Calls' = SUM(Calls),
            'Connects' = SUM(Connects),
            'Contacts' = SUM(Contacts),
            'Sales' = SUM(Sales),
            'ThresholdCalls' = SUM(ThresholdCalls),
            'ThresholdSales' = SUM(ThresholdSales)
        FROM (
        ";

        $union = '';
        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id' . $i] =  Auth::user()->group_id;
            $bind['startdate' . $i] = $startDate;
            $bind['enddate' . $i] = $endDate;
            $bind['threshold1' . $i] = $this->params['threshold_secs'];
            $bind['threshold2' . $i] = $this->params['threshold_secs'];

            $sql .= " $union SELECT DR.Rep, RR.Skill, DR.CallStatus,
            'Calls' = 1,
            'Connects' = CASE WHEN DI.Type > 0 AND DR.Duration > 0 THEN 1 ELSE 0 END,
            'Contacts' = CASE WHEN DI.Type > 1 AND DR.Duration > 0 THEN 1 ELSE 0 END,
            'Sales' = CASE WHEN DI.Type = 3 AND DR.Duration > 0 THEN 1 ELSE 0 END,
            'ThresholdCalls' = CASE WHEN DR.Duration >= :threshold1$i THEN 1 ELSE 0 END,
            'ThresholdSales' = CASE WHEN DR.Duration >= :threshold2$i AND DI.Type = 3 THEN 1 ELSE 0 END
            FROM [$db].[dbo].[DialingResults] DR
            LEFT JOIN [$db].[dbo].[Reps] RR on RR.RepName COLLATE SQL_Latin1_General_CP1_CS_AS = DR.Rep";

            if (!empty($this->params['skills'])) {
                $sql .= "
                INNER JOIN #SelectedSkill SS on SS.SkillName COLLATE SQL_Latin1_General_CP1_CS_AS = RR.Skill";
            }

            $sql .= "
            LEFT JOIN [$db].[dbo].[Dispos] DI ON DI.id = DR.DispositionId
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
        GROUP BY Rep, Skill, CallStatus";

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
                    'Skill' => $rec['Skill'],
                    'ManHours' => 0,
                    'LoggedInTime' => 0,
                    'Stats' => [],
                    'Connects' => 0,
                    'Contacts' => 0,
                    'ContsPerHour' => 0,
                    'Sales' => 0,
                    'APH' => 0,
                    'Calls' => 0,
                    'ThresholdSales' => 0,
                    'ThresholdCalls' => 0,
                    'ThresholdRatio' => 0,
                    'ThresholdClosingPct' => 0,
                ];
            }

            $reps[$rec['Rep']]['Connects'] += $rec['Connects'];
            $reps[$rec['Rep']]['Contacts'] += $rec['Contacts'];
            $reps[$rec['Rep']]['Sales'] += $rec['Sales'];
            $reps[$rec['Rep']]['ThresholdCalls'] += $rec['ThresholdCalls'];
            $reps[$rec['Rep']]['Calls'] += $rec['Calls'];
            $reps[$rec['Rep']]['ThresholdSales'] += $rec['ThresholdSales'];

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
            'Skill' => '',
            'ManHours' => 0,
            'LoggedInTime' => 0,
        ];

        // Columns are variable, so set them now
        $this->params['columns'] = [
            'Rep' => trans('reports.rep'),
            'Skill' => trans('reports.skill'),
            'ManHours' => trans('reports.manhours'),
            'LoggedInTime' => trans('reports.loggedintime'),
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
        $this->params['columns'][] = trans('reports.threshold_calls');
        $this->params['columns'][] = trans('reports.threshold_ratio');
        $this->params['columns'][] = trans('reports.threshold_closing_pct');

        // Finish blank record
        $zerorec['Connects'] = 0;
        $zerorec['Contacts'] = 0;
        $zerorec['ContsPerHour'] = 0;
        $zerorec['Sales'] = 0;
        $zerorec['APH'] = 0;
        $zerorec['Calls'] = 0;
        $zerorec['ThresholdSales'] = 0;
        $zerorec['ThresholdCalls'] = 0;
        $zerorec['ThresholdRatio'] = 0;
        $zerorec['ThresholdClosingPct'] = 0;

        // Create totals record
        $total = $zerorec;
        $total['Rep'] = trans('reports.total') . ':';

        foreach ($reps as $rep => $reprec) {
            $row = $zerorec;

            $row['Rep'] = $rep;
            $row['Skill'] = $reprec['Skill'];
            $row['ManHours'] = $reprec['ManHours'];
            $row['LoggedInTime'] = $reprec['LoggedInTime'];
            $row['Connects'] = $reprec['Connects'];
            $row['Contacts'] = $reprec['Contacts'];
            $row['Sales'] = $reprec['Sales'];
            $row['ThresholdCalls'] = $reprec['ThresholdCalls'];
            $row['Calls'] = $reprec['Calls'];
            $row['ThresholdSales'] = $reprec['ThresholdSales'];

            // Add to totals
            $total['ManHours'] += $reprec['ManHours'];
            $total['LoggedInTime'] += $reprec['LoggedInTime'];
            $total['Connects'] += $reprec['Connects'];
            $total['Contacts'] += $reprec['Contacts'];
            $total['Sales'] += $reprec['Sales'];
            $total['ThresholdCalls'] += $reprec['ThresholdCalls'];
            $total['Calls'] += $reprec['Calls'];
            $total['ThresholdSales'] += $reprec['ThresholdSales'];

            foreach ($reprec['Stats'] as $call_status => $count) {
                $row[$call_status] = $count;
                $total[$call_status] += $count;
            }

            // Do calcs
            $row['ManHours'] = number_format($row['ManHours'] / 60 / 60, 2);
            $row['LoggedInTime'] = $this->secondsToHms($row['LoggedInTime']);

            $row['ThresholdRatio'] = number_format($row['Calls'] == 0 ? 0 : $row['ThresholdCalls'] / $row['Calls'] * 100, 2) . '%';
            $row['ThresholdClosingPct'] = number_format($row['ThresholdCalls'] == 0 ? 0 : $row['ThresholdSales'] / $row['ThresholdCalls'] * 100, 2) . '%';

            if ($row['ManHours'] == 0) {
                $row['ContsPerHour'] = 0;
                $row['APH'] = 0;
            } else {
                $row['ContsPerHour'] = round($row['Contacts'] / $row['ManHours'], 2);
                $row['APH'] = round($row['Sales'] / $row['ManHours'], 2);
            }

            unset($row['Calls']);
            unset($row['ThresholdSales']);

            $results[] = $row;
        }

        // Do calcs
        $total['ThresholdRatio'] = number_format($total['Calls'] == 0 ? 0 : $total['ThresholdCalls'] / $total['Calls'] * 100, 2) . '%';
        $total['ThresholdClosingPct'] = number_format($total['ThresholdCalls'] == 0 ? 0 : $total['ThresholdSales'] / $total['ThresholdCalls'] * 100, 2) . '%';

        if ($total['ManHours'] == 0) {
            $total['ContsPerHour'] = 0;
            $total['APH'] = 0;
        } else {
            $total['ContsPerHour'] = round($total['Contacts'] / $total['ManHours'], 2);
            $total['APH'] = round($total['Sales'] / $total['ManHours'], 2);
        }

        $total['ManHours'] = number_format($total['ManHours'] / 60 / 60, 2);
        $total['LoggedInTime'] = $this->secondsToHms($total['LoggedInTime']);

        unset($total['Calls']);
        unset($total['ThresholdSales']);

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

        if (!empty($request->skills)) {
            $this->params['skills'] = $request->skills;
        }

        if (empty($request->threshold_secs)) {
            $this->errors->add('threshold_secs.required', trans('reports.errthresholdrequired'));
        } else {
            $this->params['threshold_secs'] = $request->threshold_secs;
        }

        // Save params to session
        $this->saveSessionParams();

        return $this->errors;
    }
}
