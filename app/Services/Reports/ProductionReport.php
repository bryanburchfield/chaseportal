<?php

namespace App\Services\Reports;

use App\Traits\CampaignTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\ReportTraits;
use Illuminate\Support\Facades\Log;

class ProductionReport
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

        // Reps table
        $reps = [];

        // Get Rep hours worked
        $sql = "SET NOCOUNT ON;";

        if (!empty($this->params['skills'])) {
            $sql .= "
            CREATE TABLE #SelectedSkill(SkillName varchar(50) Primary Key);
            INSERT INTO #SelectedSkill SELECT DISTINCT [value] from dbo.SPLIT('$skills', '!#!');";
        }

        $sql .= "
        SELECT Rep, SUM(ManHours) ManHours FROM (";

        $bind = [];
        $union = '';
        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id' . $i] =  Auth::user()->group_id;
            $bind['startdate' . $i] = $startDate;
            $bind['enddate' . $i] = $endDate;

            $sql .= " $union SELECT Rep, sum(Duration) ManHours
            FROM [$db].[dbo].[AgentActivity]
            WHERE GroupId = :group_id$i
            AND date >= :startdate$i
            AND date < :enddate$i
            AND [Action] NOT IN ('Paused','Login','Logout')";

            if (!empty($campaigns)) {
                $bind['campaigns' . $i] = $campaigns;
                $sql .= " AND Campaign in (SELECT value FROM dbo.SPLIT(:campaigns$i, '!#!'))";
            }

            if (!empty($this->params['skills'])) {
                $sql .= "
                INNER JOIN [$db].[dbo].[Reps] RR on RR.RepName COLLATE SQL_Latin1_General_CP1_CS_AS = AA.Rep
                INNER JOIN #SelectedSkill SS on SS.SkillName COLLATE SQL_Latin1_General_CP1_CS_AS = RR.Skill";
            }

            $sql .= "GROUP BY Rep";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp
            GROUP BY Rep";

        $results = $this->runSql($sql, $bind);

        foreach ($results as $rec) {
            $reps[$rec['Rep']] = [
                'Rep' => $rec['Rep'],
                'ManHours' => round($rec['ManHours'] / 60 / 60, 2),
            ];
        }







        if (empty($results)) {
            $this->params['totrows'] = 0;
            $this->params['totpages'] = 1;
            $this->params['curpage'] = 1;
        } else {
            $this->params['totrows'] = count($results);
            $this->params['totpages'] = floor($this->params['totrows'] / $this->params['pagesize']);
            $this->params['totpages'] += floor($this->params['totrows'] / $this->params['pagesize']) == ($this->params['totrows'] / $this->params['pagesize']) ? 0 : 1;
            $results = $this->processResults($results);
        }

        return $this->getPage($results, $all);
    }

    private function processResults($results)
    {
        if (!count($results)) return $results;

        // Columns are variable, so set them now
        $this->params['columns'] = [];
        $total = [];

        foreach ($results[0] as $k => $v) {
            switch ($k) {
                case 'Rep':
                    $k = trans('reports.rep');
                    break;
                case 'ManHours':
                    $k = trans('reports.manhours');
                    break;
            }
            $this->params['columns'][] = $k;
        }

        // this sets the order of the columns
        foreach ($this->params['columns'] as $k => $v) {
            $total[$v] = '';
        }

        foreach ($this->params['columns'] as $k) {
            $total[$k] = 0;
        }
        $total[trans('reports.rep')] = trans('reports.total') . ':';

        foreach ($results as &$rec) {
            foreach ($rec as $k => $v) {
                if ($k != 'Rep') {
                    $k = ($k == 'ManHours') ? trans('reports.manhours') : $k;
                    $total[$k] += $v;
                }
            }
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

        if (!empty($request->skills)) {
            $this->params['skills'] = $request->skills;
        }

        // Save params to session
        $this->saveSessionParams();

        return $this->errors;
    }
}
