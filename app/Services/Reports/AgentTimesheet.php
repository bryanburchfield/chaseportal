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

        $this->params['reportName'] = 'reports.agent_timesheet';
        $this->params['nostreaming'] = 1;
        $this->params['reps'] = [];
        $this->params['skills'] = [];
        $this->params['detailed'] = 0;
        $this->params['hasTotals'] = true;
        $this->params['columns'] = [
            'Rep' => 'reports.rep',
            'Campaign' => 'reports.campaign',
            'LogInTime' => 'reports.logintime',
            'LogOutTime' => 'reports.logouttime',
            'LoggedInSec' => 'reports.loggedintime',
            'ManHourSec' => 'reports.manhoursec',
            'PausedTimeSec' => 'reports.pausedtimesec',
        ];
    }

    public function getFilters()
    {
        $filters = [
            'reps' => $this->getAllReps(),
            'skills' => $this->getAllSkills(),
            'detailed' => 0,
            'db_list' => Auth::user()->getDatabaseArray(),
        ];

        return $filters;
    }

    public function getInfo()
    {
        return [
            'columns' => $this->params['columns'],
            'paragraphs' => 1,
        ];
    }

    private function executeReport($all = false)
    {
        $this->setHeadings();

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
            AND AA.Date < :enddate$i
            AND (AA.Duration > 0 OR AA.Action IN ('Login','Logout'))";

            if (!empty($reps)) {
                $bind['reps' . $i] = $reps;
                $sql .= "
                AND AA.Rep COLLATE SQL_Latin1_General_CP1_CS_AS IN (SELECT DISTINCT [value] FROM dbo.SPLIT(:reps$i, '!#!'))";
            }

            if (session('ssoRelativeCampaigns', 0)) {
                $sql .= " AND AA.Campaign IN (SELECT CampaignName FROM dbo.GetAllRelativeCampaigns(:ssousercamp$i, 1))";
                $bind['ssousercamp' . $i] = session('ssoUsername');
            }

            if (session('ssoRelativeReps', 0)) {
                $sql .= " AND AA.Rep IN (SELECT RepName FROM dbo.GetAllRelativeReps(:ssouserrep$i))";
                $bind['ssouserrep' . $i] = session('ssoUsername');
            }

            $union = 'UNION';
        }
        $sql .= " ORDER BY Rep, Campaign, Date";

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

        $oldrow = '';
        $i = 0;
        foreach ($this->yieldSql($sql, $bind) as $rec) {
            $currow = $rec['Rep'] . $rec['Campaign'];

            if ($currow != $oldrow) {
                $i++;
                $oldrow = $currow;
                $loggedin = false;
                $tmpsheet[$i]['Rep'] = $rec['Rep'];
                $tmpsheet[$i]['Campaign'] = $rec['Campaign'];
                $tmpsheet[$i]['LogInTime'] = '';
                $tmpsheet[$i]['LogOutTime'] = '';
                $tmpsheet[$i]['LoggedInSec'] = 0;
                $tmpsheet[$i]['ManHourSec'] = 0;
                $tmpsheet[$i]['PausedTimeSec'] = 0;
            }

            if ($rec['Duration'] > 0) {
                $tmpsheet[$i]['LoggedInSec'] += $rec['Duration'];
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
                        $oldrow = '';  // force a new record
                    }
                    break;
                case 'Paused':
                    $tmpsheet[$i]['PausedTimeSec'] += $rec['Duration'];
                    break;
                default:
                    $tmpsheet[$i]['ManHourSec'] += $rec['Duration'];
            }
        }

        // remove any rows that don't have login and logout times
        $results = [];
        foreach ($tmpsheet as $rec) {
            // if ($rec['LogInTime'] != '' || $rec['LogOutTime'] != '') {
            $results[] = $rec;
            // }
        }

        // this sets the order of the columns
        foreach ($this->params['columns'] as $k => $v) {
            $reptotal[$k] = '';
            $total[$k] = '';
        }

        // final results
        $final = [];

        $total['Rep'] = 'Total:';
        $total['LoggedInSec'] = 0;
        $total['ManHourSec'] = 0;
        $total['PausedTimeSec'] = 0;

        // Subtotal by Rep
        $oldrep = '';

        foreach ($results as $rec) {
            if ($oldrep != $rec['Rep']) {
                if ($oldrep != '') {
                    // format totals
                    if ($reptotal['LogInTime'] != '') {
                        $reptotal['LogInTime'] = Carbon::parse($reptotal['LogInTime'])->isoFormat('L LT');
                    }
                    if ($reptotal['LogOutTime'] != '') {
                        $reptotal['LogOutTime'] = Carbon::parse($reptotal['LogOutTime'])->isoFormat('L LT');
                    }
                    $reptotal['LoggedInSec'] = $this->secondsToHms($reptotal['LoggedInSec']);
                    $reptotal['ManHourSec'] = $this->secondsToHms($reptotal['ManHourSec']);
                    $reptotal['PausedTimeSec'] = $this->secondsToHms($reptotal['PausedTimeSec']);
                    $final[] = $reptotal;
                }

                $oldrep = $rec['Rep'];

                if (!$this->params['detailed']) {
                    $reptotal['Rep'] = $rec['Rep'];
                }

                $reptotal['LoggedInSec'] = 0;
                $reptotal['ManHourSec'] = 0;
                $reptotal['PausedTimeSec'] = 0;
                $reptotal['LogInTime'] = '';
                $reptotal['LogOutTime'] = '';
            }

            $total['LoggedInSec'] += $rec['LoggedInSec'];
            $total['ManHourSec'] += $rec['ManHourSec'];
            $total['PausedTimeSec'] += $rec['PausedTimeSec'];

            $reptotal['LoggedInSec'] += $rec['LoggedInSec'];
            $reptotal['ManHourSec'] += $rec['ManHourSec'];
            $reptotal['PausedTimeSec'] += $rec['PausedTimeSec'];

            // set min logintime and max logout time if summary
            if (!$this->params['detailed']) {

                if ($rec['LogInTime'] != '') {
                    if ($reptotal['LogInTime'] == '') {
                        $reptotal['LogInTime'] = $rec['LogInTime'];
                    } elseif (Carbon::parse($rec['LogInTime'])->lt(Carbon::parse($reptotal['LogInTime']))) {
                        $reptotal['LogInTime'] = $rec['LogInTime'];
                    }
                }

                if ($rec['LogOutTime'] != '') {
                    if ($reptotal['LogOutTime'] == '') {
                        $reptotal['LogOutTime'] = $rec['LogOutTime'];
                    } elseif (Carbon::parse($rec['LogOutTime'])->gt(Carbon::parse($reptotal['LogOutTime']))) {
                        $reptotal['LogOutTime'] = $rec['LogOutTime'];
                    }
                }
            }

            if ($rec['LogInTime'] != '') {
                $rec['LogInTime'] = Carbon::parse($rec['LogInTime'])->isoFormat('L LT');
            }
            if ($rec['LogOutTime'] != '') {
                $rec['LogOutTime'] = Carbon::parse($rec['LogOutTime'])->isoFormat('L LT');
            }

            $rec['LoggedInSec'] = $this->secondsToHms($rec['LoggedInSec']);
            $rec['ManHourSec'] = $this->secondsToHms($rec['ManHourSec']);
            $rec['PausedTimeSec'] = $this->secondsToHms($rec['PausedTimeSec']);

            if ($this->params['detailed']) {
                $final[] = $rec;
            }
        }

        if ($oldrep != '') {
            // format totals
            if ($reptotal['LogInTime'] != '') {
                $reptotal['LogInTime'] = Carbon::parse($reptotal['LogInTime'])->isoFormat('L LT');
            }
            if ($reptotal['LogOutTime'] != '') {
                $reptotal['LogOutTime'] = Carbon::parse($reptotal['LogOutTime'])->isoFormat('L LT');
            }
            $reptotal['LoggedInSec'] = $this->secondsToHms($reptotal['LoggedInSec']);
            $reptotal['ManHourSec'] = $this->secondsToHms($reptotal['ManHourSec']);
            $reptotal['PausedTimeSec'] = $this->secondsToHms($reptotal['PausedTimeSec']);
            $final[] = $reptotal;
        }

        // format totals
        $total['LoggedInSec'] = $this->secondsToHms($total['LoggedInSec']);
        $total['ManHourSec'] = $this->secondsToHms($total['ManHourSec']);
        $total['PausedTimeSec'] = $this->secondsToHms($total['PausedTimeSec']);

        // Tack on the totals row
        $final[] = $total;

        return $final;
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

        $this->params['detailed'] = !empty($request->detailed);

        // Save params to session
        $this->saveSessionParams();

        return $this->errors;
    }
}
