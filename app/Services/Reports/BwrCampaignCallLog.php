<?php

namespace App\Services\Reports;

use App\Traits\BwrTraits;
use App\Traits\CampaignTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\ReportTraits;
use Illuminate\Support\Carbon;

class BwrCampaignCallLog
{
    use ReportTraits;
    use CampaignTraits;
    use BwrTraits;

    public function __construct()
    {
        $this->initilaizeParams();

        $this->params['reportName'] = 'reports.campaign_call_log';
        $this->params['nostreaming'] = 1;
        $this->params['campaigns'] = [];
        $this->params['data_sources_primary'] = [];
        $this->params['data_sources_secondary'] = [];
        $this->params['programs'] = [];
        $this->params['reps'] = [];
        $this->params['skills'] = [];
        $this->params['hasTotals'] = true;
        $this->params['columns'] = [
            'CallStatus' => 'reports.callstatus',
            'Description' => 'reports.description',
            'Cnt' => 'reports.cnt',
            'Pct' => 'reports.pct',
        ];
    }

    public function getFilters()
    {
        $filters = [
            'reps' => $this->getAllReps(),
            'skills' => $this->getAllSkills(),
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
            'paragraphs' => 3,
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
        $reps = str_replace("'", "''", implode('!#!', $this->params['reps']));

        $tz = Auth::user()->tz;

        $bind = [];

        $sql = "SET NOCOUNT ON;";

        if (!empty($this->params['skills'])) {
            $list = str_replace("'", "''", implode('!#!', $this->params['skills']));
            $sql .= "
            CREATE TABLE #SelectedSkill(SkillName varchar(50) Primary Key);
            INSERT INTO #SelectedSkill SELECT DISTINCT [value] from [dbo].SPLIT('$list', '!#!');";
        }

        $sql .= "
        SELECT COUNT(DISTINCT Rep) TotReps, SUM(ManHours) ManHours FROM (";

        $union = '';
        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id' . $i] =  Auth::user()->group_id;
            $bind['startdate' . $i] = $startDate;
            $bind['enddate' . $i] = $endDate;

            $sql .= " $union SELECT AA.Rep, sum(AA.Duration) ManHours
            FROM [$db].[dbo].[AgentActivity] AA";

            if (!empty($this->params['skills'])) {
                $sql .= "
                INNER JOIN [$db].[dbo].[Reps] RR on RR.RepName COLLATE SQL_Latin1_General_CP1_CS_AS = AA.Rep
                INNER JOIN #SelectedSkill SS on SS.SkillName COLLATE SQL_Latin1_General_CP1_CS_AS = RR.Skill";
            }

            $sql .= "
            WHERE AA.GroupId = :group_id$i
            AND AA.date >= :startdate$i
            AND AA.date < :enddate$i
            AND AA.[Action] NOT IN ('Paused','Login','Logout')";

            if (!empty($campaigns)) {
                $bind['campaigns' . $i] = $campaigns;
                $sql .= " AND AA.Campaign in (SELECT value FROM dbo.SPLIT(:campaigns$i, '!#!'))";
            }

            if (!empty($reps)) {
                $bind['reps' . $i] = $reps;
                $sql .= " AND AA.Rep in (SELECT value COLLATE SQL_Latin1_General_CP1_CS_AS FROM dbo.SPLIT(:reps$i, '!#!'))";
            }

            if (session('ssoRelativeCampaigns', 0)) {
                $sql .= " AND AA.Campaign IN (SELECT CampaignName FROM dbo.GetAllRelativeCampaigns(:ssousercamp$i, 1))";
                $bind['ssousercamp' . $i] = session('ssoUsername');
            }

            if (session('ssoRelativeReps', 0)) {
                $sql .= " AND AA.Rep IN (SELECT RepName FROM dbo.GetAllRelativeReps(:ssouserrep$i))";
                $bind['ssouserrep' . $i] = session('ssoUsername');
            }

            $sql .= "GROUP BY AA.Rep";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp";

        $summ = $this->runSql($sql, $bind);

        $this->extras['summary']['TotReps'] = $summ[0]['TotReps'];
        $this->extras['summary']['ManHours'] = $this->secondsToHms($summ[0]['ManHours']);

        $bind = [];

        // do this as 2nd query since it needs a yield() statement
        $sql = "SET NOCOUNT ON;";

        if (!empty($this->params['skills'])) {
            $list = str_replace("'", "''", implode('!#!', $this->params['skills']));
            $sql .= "
            CREATE TABLE #SelectedSkill(SkillName varchar(50) Primary Key);
            INSERT INTO #SelectedSkill SELECT DISTINCT [value] from [dbo].SPLIT('$list', '!#!');";
        }

        if (!empty($this->params['data_sources_primary'])) {
            $data_sources_primary = str_replace("'", "''", implode('!#!', $this->params['data_sources_primary']));
            $bind['data_sources_primary'] = $data_sources_primary;

            $sql .= "
            CREATE TABLE #SelectedPrimary(Data_Source_Primary varchar(255) Primary Key);
            INSERT INTO #SelectedPrimary SELECT DISTINCT [value] from [dbo].SPLIT(:data_sources_primary, '!#!');";
        }

        if (!empty($this->params['data_sources_secondary'])) {
            $data_sources_secondary = str_replace("'", "''", implode('!#!', $this->params['data_sources_secondary']));
            $bind['data_sources_secondary'] = $data_sources_secondary;

            $sql .= "
            CREATE TABLE #SelectedSecondary(Data_Source_Secondary varchar(255) Primary Key);
            INSERT INTO #SelectedSecondary SELECT DISTINCT [value] from [dbo].SPLIT(:data_sources_secondary, '!#!');";
        }

        if (!empty($this->params['programs'])) {
            $programs = str_replace("'", "''", implode('!#!', $this->params['programs']));
            $bind['programs'] = $programs;

            $sql .= "
            CREATE TABLE #SelectedProgram(Program varchar(255) Primary Key);
            INSERT INTO #SelectedProgram SELECT DISTINCT [value] from [dbo].SPLIT(:programs, '!#!');";
        }

        $union = '';
        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id1' . $i] =  Auth::user()->group_id;
            $bind['startdate1' . $i] = $startDate;
            $bind['enddate1' . $i] = $endDate;

            $sql .= " $union SELECT
            CONVERT(datetimeoffset, DR.Date) AT TIME ZONE '$tz' as Date,
            DR.Rep,
            DR.CallStatus,
            DI.Description,
            DI.IsSystem
            FROM [$db].[dbo].[DialingResults] DR
            INNER JOIN [$db].[dbo].[Leads] L ON L.id = DR.LeadId 
            INNER JOIN [$db].[dbo].[ADVANCED_BWR_Master_Table] A ON A.LeadID = L.IdGuid
            LEFT JOIN [$db].[dbo].[Dispos] DI ON DI.id = DR.DispositionId";

            if (!empty($this->params['skills'])) {
                $sql .= "
                INNER JOIN [$db].[dbo].[Reps] RR on RR.RepName COLLATE SQL_Latin1_General_CP1_CS_AS = DR.Rep
                INNER JOIN #SelectedSkill SS on SS.SkillName COLLATE SQL_Latin1_General_CP1_CS_AS = RR.Skill";
            }

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

            $sql .= "
            WHERE DR.GroupId = :group_id1$i
            AND DR.Date >= :startdate1$i
            AND DR.Date < :enddate1$i
            AND DR.CallStatus not in ('','CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','CR_DISCONNECTED','SMS Delivered','SMS Received')";

            if (!empty($campaigns)) {
                $bind['campaigns1' . $i] = $campaigns;
                $sql .= " AND DR.Campaign in (SELECT value FROM [dbo].SPLIT(:campaigns1$i, '!#!'))";
            }

            if (!empty($reps)) {
                $bind['reps1' . $i] = $reps;
                $sql .= " AND DR.Rep in (SELECT value COLLATE SQL_Latin1_General_CP1_CS_AS FROM [dbo].SPLIT(:reps1$i, '!#!'))";
            }

            if (session('ssoRelativeCampaigns', 0)) {
                $sql .= " AND DR.Campaign IN (SELECT CampaignName FROM dbo.GetAllRelativeCampaigns(:ssousercamp$i, 1))";
                $bind['ssousercamp' . $i] = session('ssoUsername');
            }

            if (session('ssoRelativeReps', 0)) {
                $sql .= " AND DR.Rep IN (SELECT RepName FROM dbo.GetAllRelativeReps(:ssouserrep$i))";
                $bind['ssouserrep' . $i] = session('ssoUsername');
            }

            $union = 'UNION ALL';
        }

        $results = $this->processResults($sql, $bind);

        if (empty($results)) {
            $this->params['totrows'] = 0;
            $this->params['totpages'] = 1;
            $this->params['curpage'] = 1;
            $results = [];
        } else {
            $this->params['totrows'] = 40;
            $this->params['totpages'] = floor($this->params['totrows'] / $this->params['pagesize']);
            $this->params['totpages'] += floor($this->params['totrows'] / $this->params['pagesize']) == ($this->params['totrows'] / $this->params['pagesize']) ? 0 : 1;
        }

        return $this->getPage($results, $all);
    }

    private function processResults($sql, $bind)
    {
        $stats = [];
        $callstats = [];
        $calldetails = [];

        $donut = [
            'AgentCalls' => 0,
            'SystemCalls' => 0,
        ];

        $detrec = [
            'Time' => '',
            'TotCalls' => 1,
            'HandledCalls' => 0,
        ];

        $tot = 0;
        foreach ($this->yieldSql($sql, $bind) as $rec) {

            // extras
            $detrec['Time'] = $this->roundToQuarterHour($rec['Date']);
            $detrec['HandledCalls'] = $rec['IsSystem'] == 1 ? 0 : 1;

            $key = array_search($detrec['Time'], array_column($calldetails, 'Time'));

            if ($key === false) {
                $calldetails[] = $detrec;
            } else {
                $calldetails[$key]['TotCalls'] += $detrec['TotCalls'];
                $calldetails[$key]['HandledCalls'] += $detrec['HandledCalls'];
            }

            if ($rec['IsSystem'] == 0) {
                $donut['AgentCalls']++;
            } else {
                $donut['SystemCalls']++;
            }

            $key = array_search($rec['CallStatus'], array_column($callstats, 'CallStatus'));
            if ($key === false) {
                $callstats[] = [
                    'CallStatus' => $rec['CallStatus'],
                    'Count' => 1,
                ];
            } else {
                $callstats[$key]['Count']++;
            }

            // results
            if ($tot == 0) {
                $this->extras['summary']['starttime'] = $rec['Date'];
            }
            $this->extras['summary']['stoptime'] = $rec['Date'];

            $stat = $rec['CallStatus'];
            $tot++;

            if (!array_key_exists($stat, $stats)) {
                $stats[$stat]['CallStatus'] = $stat;
                $stats[$stat]['Description'] = !empty($rec['Description']) ? $rec['Description'] : $stat;
                $stats[$stat]['Cnt'] = 0;
                $stats[$stat]['Pct'] = 0;
            }
            $stats[$stat]['Cnt']++;
        }

        $ret = [];
        foreach (array_keys($stats) as $k) {
            $stats[$k]['Pct'] = number_format($stats[$k]['Cnt'] / $tot * 100, 2);
            $ret[] = $stats[$k];
        }

        // now sort
        if (empty($this->params['orderby'])) {
            $cnt  = array_column($ret, 'Cnt');
            $stat = array_column($ret, 'CallStatus');
            array_multisort($cnt, SORT_DESC, $stat, SORT_ASC, $ret);
        } else {
            $field = key($this->params['orderby']);
            $col = array_column($ret, $field);
            $dir = $this->params['orderby'][$field] == 'desc' ? SORT_DESC : SORT_ASC;
            array_multisort($col, $dir, $ret);
        }

        // format cols
        foreach ($ret as &$rec) {
            $rec['Pct'] .= '%';
        }

        // Add total row
        $ret[] = [
            'CallStatus' => 'Total Calls:',
            'Description' => '',
            'Cnt' => $tot,
            'Pct' => '',
        ];

        // finish up extras
        // sort the results
        $col  = array_column($calldetails, 'Time');
        array_multisort($col, SORT_ASC, $calldetails);

        // fill in blanks
        $detrec = [
            'Time' => '',
            'TotCalls' => 0,
            'HandledCalls' => 0,
        ];

        if (count($calldetails)) {
            $starttime = new Carbon($calldetails[0]['Time']);
            $endtime = new Carbon($calldetails[count($calldetails) - 1]['Time']);

            while ($starttime < $endtime) {
                $time = $starttime->format('H:i');
                $key = array_search($time, array_column($calldetails, 'Time'));
                if ($key === false) {
                    $detrec['Time'] = $time;
                    $calldetails[] = $detrec;
                }
                $starttime->modify('+15 minutes');
            }
        }

        // sort the results again
        $col  = array_column($calldetails, 'Time');
        array_multisort($col, SORT_ASC, $calldetails);

        $this->extras['calldetails'] = $calldetails;
        $this->extras['donut'] = $donut;
        $this->extras['stats'] = $callstats;

        return array_values($ret);
    }

    private function processInput(Request $request)
    {
        // Get vals from session if not set (for exports)
        $request = $this->getSessionParams($request);

        // Check page filters
        $this->checkPageFilters($request);

        // Check report filters
        $this->checkDateRangeFilters($request);

        if (!empty($request->data_sources_primary)) {
            $this->params['data_sources_primary'] = $request->data_sources_primary;
        }

        if (!empty($request->data_sources_secondary)) {
            $this->params['data_sources_secondary'] = $request->data_sources_secondary;
        }

        if (!empty($request->programs)) {
            $this->params['programs'] = $request->programs;
        }

        if (!empty($request->reps)) {
            $this->params['reps'] = $request->reps;
        }

        if (!empty($request->skills)) {
            $this->params['skills'] = $request->skills;
        }

        if (!empty($request->campaigns)) {
            $this->params['campaigns'] = $request->campaigns;
        }

        // Save params to session
        $this->saveSessionParams();

        return $this->errors;
    }

    private function roundToQuarterHour($timestring)
    {
        $dt = new Carbon($timestring);
        $minute = $dt->format('i');
        return $dt->modify('-' . ($minute % 15) . 'minutes')->format('H:i');
    }
}
