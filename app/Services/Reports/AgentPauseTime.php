<?php

namespace App\Services\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\ReportTraits;
use Illuminate\Support\Carbon;

class AgentPauseTime
{
    use ReportTraits;

    public function __construct()
    {
        $this->initilaizeParams();

        $this->params['reportName'] = trans('reports.agent_pause_time');
        $this->params['fromdate'] = date("m/d/Y 9:00 \A\M");
        $this->params['todate'] = date("m/d/Y 8:00 \P\M");
        $this->params['reps'] = [];
        $this->params['skills'] = [];
        $this->params['columns'] = [
            'Rep' => trans('reports.rep'),
            'Campaign' => trans('reports.campaign'),
            'LogInTime' => trans('reports.logintime'),
            'LogOutTime' => trans('reports.logouttime'),
            'PausedTime' => trans('reports.pausedtime'),
            'UnPausedTime' => trans('reports.unpausedtime'),
            'PausedTimeSec' => trans('reports.pausedtimesec'),
            'BreakCode' => trans('reports.breakcode'),
            'TotPausedSec' => trans('reports.totpausedsec'),
            'TotManHours' => trans('reports.totmanhours'),
        ];
    }

    public function getFilters()
    {
        $filters = [
            'reps' => $this->getAllReps(true),
            'skills' => $this->getAllSkills(),
            'db_list' => Auth::user()->getDatabaseArray(),
        ];

        return $filters;
    }

    private function executeReport($all = false)
    {
        list($fromDate, $toDate) = $this->dateRange($this->params['fromdate'], $this->params['todate']);

        $tz =  Auth::user()->tz;

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');
        $reps = str_replace("'", "''", implode('!#!', $this->params['reps']));

        $sql = 'SET NOCOUNT ON;';

        if (!empty($this->params['skills'])) {
            $list = str_replace("'", "''", implode('!#!', $this->params['skills']));
            $sql .= "
            CREATE TABLE #SelectedSkill(SkillName varchar(50) Primary Key);
            INSERT INTO #SelectedSkill SELECT DISTINCT [value] from dbo.SPLIT('$list', '!#!');";
        }

        $union = '';
        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id' . $i] =  Auth::user()->group_id;
            $bind['startdate' . $i] = $startDate;
            $bind['enddate' . $i] = $endDate;

            $sql .= " $union SELECT CONVERT(datetimeoffset, AA.Date) AT TIME ZONE '$tz' as Date,
            AA.Campaign, AA.Rep, [Action], AA.Duration, AA.Details, AA.id
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
                $sql .= " AND Rep COLLATE SQL_Latin1_General_CP1_CS_AS IN (SELECT DISTINCT [value] FROM dbo.SPLIT(:reps$i, '!#!'))";
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

        return $this->getPage($results);
    }

    private function processResults($sql, $bind)
    {
        // loop thru results looking for log in/out times
        // then do our sorting
        // finally, format fields

        // primary key is rep/camp/login
        // recs for paused/unpaused
        // total up manhours

        $tmparray = [];
        $idarray = [];
        $blankrec = [
            'Rep' => '',
            'Campaign' => '',
            'LogInTime' => '',
            'LogOutTime' => '',
            'TotPausedSec' => 0,
            'TotManHours' => 0,
            'PauseRecs' => [],
        ];

        $i = 0;
        foreach ($this->yieldSql($sql, $bind) as $rec) {

            if ($i == 0) {
                $i++;
                $tmparray[$i] = $blankrec;
                $tmparray[$i]['Rep'] = $rec['Rep'];
                $tmparray[$i]['Campaign'] = $rec['Campaign'];
            } else {
                if ($rec['Rep'] != $tmparray[$i]['Rep'] || $rec['Campaign'] != $tmparray[$i]['Campaign'] || !empty($tmparray[$i]['LogOutTime'])) {
                    $i++;
                    $tmparray[$i] = $blankrec;
                    $tmparray[$i]['Rep'] = $rec['Rep'];
                    $tmparray[$i]['Campaign'] = $rec['Campaign'];
                }
            }

            switch ($rec['Action']) {
                case 'Login':
                    if (empty($tmparray[$i]['LogInTime'])) {
                        $tmparray[$i]['LogInTime'] = $rec['Date'];
                    }
                    break;
                case 'Logout':
                    if (!empty($tmparray[$i]['LogInTime'])) {
                        $tmparray[$i]['LogOutTime'] = $rec['Date'];
                    }
                    break;
                case 'Paused':
                    if (!empty($tmparray[$i]['LogInTime']) && round($rec['Duration']) > 0) {
                        $tmparray[$i]['TotPausedSec'] += $rec['Duration'];
                        $tmparray[$i]['PauseRecs'][] = $rec['id'];
                        $idarray[] = [
                            'id' => $rec['id'],
                            'Date' => substr($rec['Date'], 0, 26),  // strip offest
                            'Duration' => round($rec['Duration']),
                            'Details' => $rec['Details'],
                        ];
                    }
                    break;
                default:
                    if (!empty($tmparray[$i]['LogInTime'])) {
                        $tmparray[$i]['TotManHours'] += $rec['Duration'];
                    }
            }
        }

        // remove any rows that don't have both login and logout times or no paused or no manhours
        $outerarray = [];
        foreach ($tmparray as $rec) {
            if (!empty($rec['LogInTime']) && !empty($rec['LogOutTime']) && round($rec['TotPausedSec']) > 0 && round($rec['TotManHours']) > 0) {
                $outerarray[] = $rec;
            }
        }

        // Fill in the pause records
        $results = [];
        $i = -1;
        foreach ($outerarray as $reprec) {
            foreach ($reprec['PauseRecs'] as $id) {
                $key = array_search($id, array_column($idarray, 'id'));

                $pausedTime = date('Y-m-d H:i:s', strtotime($idarray[$key]['Date']) - $idarray[$key]['Duration']);

                $i++;
                $results[$i]['Rep'] = $reprec['Rep'];
                $results[$i]['Campaign'] = $reprec['Campaign'];
                $results[$i]['LogInTime'] = $reprec['LogInTime'];
                $results[$i]['LogOutTime'] = $reprec['LogOutTime'];

                $results[$i]['PausedTime'] = $pausedTime;
                $results[$i]['UnPausedTime'] = $idarray[$key]['Date'];
                $results[$i]['PausedTimeSec'] = $idarray[$key]['Duration'];
                $results[$i]['BreakCode'] = $idarray[$key]['Details'];

                $results[$i]['TotPausedSec'] = $reprec['TotPausedSec'];
                $results[$i]['TotManHours'] = $reprec['TotManHours'];
            }
        }

        // now sort
        if (!empty($this->params['orderby'])) {
            $field = key($this->params['orderby']);
            $dir = $this->params['orderby'][$field] == 'desc' ? SORT_DESC : SORT_ASC;
            $col = array_column($results, $field);
            array_multisort($col, $dir, $results);
        }

        // format fields
        foreach ($results as &$rec) {
            $rec['LogInTime'] = Carbon::parse($rec['LogInTime'])->format('m/d/Y h:i:s A');
            $rec['LogOutTime'] = Carbon::parse($rec['LogOutTime'])->format('m/d/Y h:i:s A');
            $rec['PausedTime'] = Carbon::parse($rec['PausedTime'])->format('m/d/Y h:i:s A');
            $rec['UnPausedTime'] = Carbon::parse($rec['UnPausedTime'])->format('m/d/Y h:i:s A');

            $rec['PausedTimeSec'] = $this->secondsToHms($rec['PausedTimeSec']);
            $rec['TotPausedSec'] = $this->secondsToHms($rec['TotPausedSec']);
            $rec['TotManHours'] = $this->secondsToHms($rec['TotManHours']);
        }

        return $results;
    }

    private function processInput(Request $request)
    {
        // Save params to session
        $this->saveSessionParams();

        // Check page filters
        $this->checkPageFilters($request);

        // Check report filters
        $this->checkDateRangeFilters($request);

        if (!empty($request->reps)) {
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
