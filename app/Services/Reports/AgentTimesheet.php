<?php

namespace App\Services\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\ReportTraits;
use Illuminate\Support\Carbon;

class AgentTimesheet
{
    use ReportTraits;

    public function __construct()
    {
        $this->initilaizeParams();

        $this->params['reportName'] = 'Agent Timesheet';
        $this->params['fromdate'] = date("m/d/Y 9:00 \A\M");
        $this->params['todate'] = date("m/d/Y 8:00 \P\M");
        $this->params['reps'] = [];
        $this->params['skills'] = [];
        $this->params['hasTotals'] = true;
        $this->params['columns'] = [
            'Date' => 'Date',
            'Rep' => 'Rep',
            'Campaign' => 'Campaign',
            'LogInTime' => 'LogIn Time',
            'LogOutTime' => 'LogOut Time',
            'ManHourSec' => 'Man Hours',
            'PausedTimeSec' => 'Paused Time',
        ];
    }

    public function getFilters()
    {
        $filters = [
            'reps' => $this->getAllReps(),
            'skills' => $this->getAllSkills(),
            'db_list' => Auth::user()->getDatabaseArray(),
        ];

        return $filters;
    }

    private function executeReport($all = false)
    {
        list($fromDate, $toDate) = $this->dateRange($this->params['fromdate'], $this->params['todate']);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');
        $reps = str_replace("'", "''", implode('!#!', $this->params['reps']));
        $skills = str_replace("'", "''", implode('!#!', $this->params['skills']));

        $tz =  Auth::user()->tz;

        $sql = 'SET NOCOUNT ON;';

        if (!empty($this->params['skills'])) {
            $bind['skills'] = $skills;

            $sql .= "
            CREATE TABLE #SelectedSkill(SkillName varchar(50) Primary Key);
            INSERT INTO #SelectedSkill SELECT DISTINCT [value] from dbo.SPLIT(:skills, '!#!');";
        }

        $union = '';
        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id' . $i] =  Auth::user()->group_id;
            $bind['startdate' . $i] = $startDate;
            $bind['enddate' . $i] = $endDate;

            $sql .= " $union SELECT CONVERT(datetimeoffset, AA.Date) AT TIME ZONE '$tz' as Date,
            AA.Campaign, AA.Rep, [Action], AA.Duration
            FROM [$db].[dbo].[AgentActivity] AA WITH(NOLOCK)";

            if (!empty($this->params['skills'])) {
                $sql .= "
                INNER JOIN [$db].[dbo].[Reps] RR on RR.RepName COLLATE SQL_Latin1_General_CP1_CS_AS = AA.Rep
                INNER JOIN #SelectedSkill SS on SS.SkillName COLLATE SQL_Latin1_General_CP1_CS_AS = RR.Skill";
            }

            $sql .= "
            WHERE AA.GroupId = :group_id$i
            AND AA.Date >= :startdate$i
            AND AA.Date < :enddate$i";

            if (!empty($reps)) {
                $bind['reps' . $i] = $reps;
                $sql .= "
                AND AA.Rep COLLATE SQL_Latin1_General_CP1_CS_AS IN (SELECT DISTINCT [value] FROM dbo.SPLIT(:reps$i, '!#!'))";
            }

            $union = 'UNION';
        }
        $sql .= " ORDER BY Rep, Date";

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
            $rec['LogInTime'] = Carbon::parse($rec['LogInTime'])->format('m/d/Y h:i:s A');
            $rec['LogOutTime'] = Carbon::parse($rec['LogOutTime'])->format('m/d/Y h:i:s A');
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
            $this->errors->add('reps.required', "At least 1 Rep required");
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
