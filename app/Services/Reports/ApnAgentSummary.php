<?php

namespace App\Services\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\ReportTraits;

class ApnAgentSummary
{
    use ReportTraits;

    public function __construct()
    {
        $this->initilaizeParams();

        $this->params['reportName'] = 'reports.agent_summary';
        $this->params['reps'] = [];
        $this->params['skills'] = [];
        $this->params['hasTotals'] = true;
        $this->params['columns'] = [
            'Rep' => 'reports.rep',
            'Calls' => 'reports.calls',
            'Connects' => 'reports.connects',
            'Contacts' => 'reports.contacts',
            'Hours' => 'reports.hours',
            'Leads' => 'reports.leads',
            'CPH' => 'reports.cph',
            'APH' => 'reports.aph',
            'ConversionRate' => 'reports.conversionrate',
            'ConversionFactor' => 'reports.conversionfactor',
            'TalkTimeSec' => 'reports.talktimesec',
            'AvTalkTime' => 'reports.avtalktime',
            'PausedTimeSec' => 'reports.pausedtimesec',
            'WaitTimeSec' => 'reports.waittimesec',
            'AvWaitTime' => 'reports.avwaittime',
            'DispositionTimeSec' => 'reports.dispositiontimesec',
            'AvDispoTime' => 'reports.avdispotime',
            'LoggedInTime' => 'reports.loggedintime',
            'ThresholdCalls' => 'reports.threshold_calls',
            'ThresholdRatio' => 'reports.threshold_ratio',
            'ThresholdClosingPct' => 'reports.threshold_closing_pct',
        ];
    }

    public function getFilters()
    {
        $filters = [
            'reps' => $this->getAllReps(),
            'skills' => $this->getAllSkills(),
            'threshold_secs' => 300,
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
        list($sql, $bind) = $this->makeQuery($all);

        $results = $this->runSql($sql, $bind);

        if (empty($results)) {
            $this->params['totrows'] = 0;
            $this->params['totpages'] = 1;
            $this->params['curpage'] = 1;
        } else {
            $results = $this->processResults($results);
            $this->params['totrows'] = count($results);
            $this->params['totpages'] = floor($this->params['totrows'] / $this->params['pagesize']);
            $this->params['totpages'] += floor($this->params['totrows'] / $this->params['pagesize']) == ($this->params['totrows'] / $this->params['pagesize']) ? 0 : 1;
        }

        return $this->getPage($results, $all);
    }

    public function makeQuery($all)
    {
        $this->setHeadings();

        list($fromDate, $toDate) = $this->dateRange($this->params['fromdate'], $this->params['todate']);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');
        $reps = str_replace("'", "''", implode('!#!', $this->params['reps']));

        $sql = "SET NOCOUNT ON;";

        if (!empty($this->params['skills'])) {
            $list = str_replace("'", "''", implode('!#!', $this->params['skills']));
            $sql .= "
            CREATE TABLE #SelectedSkill(SkillName varchar(50) Primary Key);
            INSERT INTO #SelectedSkill SELECT DISTINCT [value] from dbo.SPLIT('$list', '!#!');";
        }

        $bind['reps'] = $reps;

        $sql .= "
        CREATE TABLE #AgentSummary(
            Rep varchar(50) COLLATE SQL_Latin1_General_CP1_CS_AS NOT NULL,
            Calls int DEFAULT 0,
            Connects int DEFAULT 0,
            Contacts int DEFAULT 0,
            Hours numeric(18,2) DEFAULT 0,
            Leads int DEFAULT 0,
            CPH numeric(18,2) DEFAULT 0,
            APH numeric(18,2) DEFAULT 0,
            ConversionRate numeric(18,2) DEFAULT 0,
            ConversionFactor numeric(18,2) DEFAULT 0,
            TalkTimeSec int DEFAULT 0,
            TalkTimeCount int DEFAULT 0,
            AvTalkTime numeric(18,2) DEFAULT 0,
            PausedTimeSec int DEFAULT 0,
            WaitTimeSec int DEFAULT 0,
            WaitTimeCount int DEFAULT 0,
            AvWaitTime numeric(18,2) DEFAULT 0,
            DispositionTimeSec int DEFAULT 0,
            DispositionTimeCount int DEFAULT 0,
            AvDispoTime numeric(18,2) DEFAULT 0,
            LoggedInTime int DEFAULT 0,
            ThresholdCalls int DEFAULT 0,
            ThresholdSales int DEFAULT 0,
            ThresholdRatio numeric(18,2) DEFAULT 0,
            ThresholdClosingPct numeric(18,2) DEFAULT 0
            );

        INSERT #AgentSummary(Rep)
        SELECT DISTINCT [value] from dbo.SPLIT(:reps, '!#!');

        SELECT * INTO #DialingResultsStats FROM (";

        $union = '';
        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id1' . $i] =  Auth::user()->group_id;
            $bind['startdate1' . $i] = $startDate;
            $bind['enddate1' . $i] = $endDate;
            $bind['threshold' . $i] = $this->params['threshold_secs'];

            $sql .= " $union SELECT Rep, [Type], SUM(OverThreshold) as OverThreshold, COUNT(*) as [Count]
            FROM
            (SELECT r.Rep, IsNull(DI.Type,0) as [Type],
                CASE WHEN r.Duration >= :threshold$i THEN 1 ELSE 0 END as OverThreshold
                FROM [$db].[dbo].[DialingResults] r WITH(NOLOCK)
                LEFT JOIN [$db].[dbo].[Dispos] DI ON DI.id = r.DispositionId
                INNER JOIN #AgentSummary sr on sr.Rep COLLATE SQL_Latin1_General_CP1_CS_AS = r.Rep";

            if (!empty($this->params['skills'])) {
                $sql .= "
                    INNER JOIN [$db].[dbo].[Reps] RR on RR.RepName COLLATE SQL_Latin1_General_CP1_CS_AS = r.Rep
                    INNER JOIN #SelectedSkill SS on SS.SkillName COLLATE SQL_Latin1_General_CP1_CS_AS = RR.Skill";
            }

            $sql .= "
                WHERE r.GroupId = :group_id1$i
                AND r.Date >= :startdate1$i
                AND r.Date < :enddate1$i
                AND r.Duration > 0";

            if (session('ssoRelativeCampaigns', 0)) {
                $sql .= " AND r.Campaign IN (SELECT CampaignName FROM dbo.GetAllRelativeCampaigns(:ssousercamp1$i, 1))";
                $bind['ssousercamp1' . $i] = session('ssoUsername');
            }

            $sql .= "
            ) a
            GROUP BY Rep, [Type], OverThreshold";

            $union = 'UNION ALL';
        }

        $sql .=
            ") tmp;

        CREATE INDEX IX_RepType ON #DialingResultsStats (Rep, [Type]);
        CREATE INDEX IX_Type ON #DialingResultsStats ([Type]);

        SELECT * INTO #AgentSummaryDuration FROM (";

        $union = '';
        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id3' . $i] =  Auth::user()->group_id;
            $bind['startdate3' . $i] = $startDate;
            $bind['enddate3' . $i] = $endDate;

            $sql .= " $union SELECT aa.Rep, [Action], SUM(aa.Duration) as Duration, COUNT(id) as [Count]
            FROM [$db].[dbo].[AgentActivity] as aa WITH(NOLOCK)
            INNER JOIN #AgentSummary r on r.Rep COLLATE SQL_Latin1_General_CP1_CS_AS = aa.Rep";

            if (!empty($this->params['skills'])) {
                $sql .= "
                INNER JOIN [$db].[dbo].[Reps] RR on RR.RepName COLLATE SQL_Latin1_General_CP1_CS_AS = aa.Rep
                INNER JOIN #SelectedSkill SS on SS.SkillName COLLATE SQL_Latin1_General_CP1_CS_AS = RR.Skill";
            }

            $sql .= "
            WHERE aa.GroupId = :group_id3$i
            AND aa.Date >= :startdate3$i
            AND aa.Date < :enddate3$i
            AND aa.Duration > 0";

            if (session('ssoRelativeCampaigns', 0)) {
                $sql .= " AND aa.Campaign IN (SELECT CampaignName FROM dbo.GetAllRelativeCampaigns(:ssousercamp3$i, 1))";
                $bind['ssousercamp3' . $i] = session('ssoUsername');
            }

            $sql .= " GROUP BY aa.Rep, [Action]";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp;

        CREATE INDEX IX_Rep ON #AgentSummaryDuration (Rep);
        CREATE INDEX IX_RepDuration ON #AgentSummaryDuration (Rep, Duration);
        CREATE INDEX IX_Action ON #AgentSummaryDuration ([Action]);
        CREATE INDEX IX_RepAction ON #AgentSummaryDuration (Rep, Duration, [Action]);

        UPDATE #AgentSummary
        SET Calls = a.cnt
        FROM (SELECT Rep, SUM([Count]) as cnt
              FROM #DialingResultsStats
              GROUP BY Rep) a
        WHERE #AgentSummary.Rep = a.Rep;

        UPDATE #AgentSummary
        SET Connects = a.cnt
        FROM (SELECT Rep, SUM([Count]) as cnt
              FROM #DialingResultsStats
              WHERE [Type] > 0
              GROUP BY Rep) a
        WHERE #AgentSummary.Rep = a.Rep;

        UPDATE #AgentSummary
        SET Contacts = a.cnt
        FROM (SELECT Rep, SUM([Count]) as cnt
              FROM #DialingResultsStats
              WHERE [Type] > 1
              GROUP BY Rep) a
        WHERE #AgentSummary.Rep = a.Rep;

        UPDATE #AgentSummary
        SET Hours = IsNull(a.Hours/3600, 0)
        FROM (SELECT aa.Rep, SUM(Duration) as Hours
              FROM #AgentSummaryDuration aa
              WHERE aa.Action <> 'Paused'
              GROUP BY aa.Rep) a
        WHERE #AgentSummary.Rep = a.Rep;

        UPDATE #AgentSummary
        SET Leads = a.Leads
        FROM (SELECT Rep, SUM([Count]) as Leads
              FROM #DialingResultsStats
              WHERE [Type] = 3
              GROUP BY Rep) a
        WHERE #AgentSummary.Rep = a.Rep;

        UPDATE #AgentSummary
        SET CPH = Connects/Hours,
            APH = Leads/Hours
        WHERE Hours > 0;

        UPDATE #AgentSummary
        SET ConversionRate = (CAST(Leads as numeric(18,2)) / CAST(Contacts as numeric(18,2))) * 100
        WHERE Contacts > 0;

        UPDATE #AgentSummary
        SET ConversionFactor = (CAST(Leads as numeric(18,2)) /CAST(Contacts as numeric(18,2))) / Hours
        WHERE Hours > 0 AND Contacts > 0;

        UPDATE #AgentSummary
        SET TalkTimeSec = a.TalkTime,
            TalkTimeCount = a.tot
        FROM (SELECT aa.Rep, SUM(Duration) as TalkTime,  SUM([Count]) as tot
              FROM #AgentSummaryDuration aa WITH(NOLOCK)
              WHERE aa.Action in ('Call', 'ManualCall', 'InboundCall')
              GROUP BY aa.Rep) a
        WHERE #AgentSummary.Rep = a.Rep;

        UPDATE #AgentSummary
        SET PausedTimeSec = a.PausedTime
        FROM (SELECT aa.Rep, SUM(Duration) as PausedTime
              FROM #AgentSummaryDuration aa
              WHERE aa.Action = 'Paused'
              GROUP BY aa.Rep) a
        WHERE #AgentSummary.Rep = a.Rep;

        UPDATE #AgentSummary
        SET WaitTimeSec = a.WaitTime,
            WaitTimeCount = a.tot
        FROM (SELECT aa.Rep, SUM(Duration) as WaitTime,  SUM([Count]) as tot
              FROM #AgentSummaryDuration aa WITH(NOLOCK)
              WHERE aa.Action = 'Waiting'
              GROUP BY aa.Rep) a
        WHERE #AgentSummary.Rep = a.Rep;

        UPDATE #AgentSummary
        SET DispositionTimeSec = a.DispositionTime,
            DispositionTimeCount = a.tot
        FROM (SELECT aa.Rep, SUM(Duration) as DispositionTime,  SUM([Count]) as tot
              FROM #AgentSummaryDuration aa WITH(NOLOCK)
              WHERE aa.Action = 'Disposition'
              GROUP BY aa.Rep) a
        WHERE #AgentSummary.Rep = a.Rep;

        UPDATE #AgentSummary
        SET LoggedInTime = a.Hours
        FROM (SELECT aa.Rep, SUM(Duration) as Hours
              FROM #AgentSummaryDuration aa
              GROUP BY aa.Rep) a
        WHERE #AgentSummary.Rep = a.Rep;

        UPDATE #AgentSummary
        SET AvWaitTime = WaitTimeSec / WaitTimeCount
        WHERE WaitTimeCount > 0;

        UPDATE #AgentSummary
        SET AvTalkTime = TalkTimeSec / TalkTimeCount
        WHERE TalkTimeCount > 0;

        UPDATE #AgentSummary
        SET AvDispoTime = DispositionTimeSec / DispositionTimeCount
        WHERE DispositionTimeCount > 0;

        UPDATE #AgentSummary
        SET ThresholdCalls = a.tot
        FROM (SELECT Rep, SUM([OverThreshold]) as tot
              FROM #DialingResultsStats
              WHERE Type > 1
              GROUP BY Rep) a
        WHERE #AgentSummary.Rep = a.Rep;

        UPDATE #AgentSummary
        SET ThresholdRatio = (CAST(ThresholdCalls as numeric(18,2)) / CAST(Connects as numeric(18,2))) * 100
        WHERE Connects > 0;

        UPDATE #AgentSummary
        SET ThresholdSales = a.tot
        FROM (SELECT Rep, SUM([OverThreshold]) as tot
              FROM #DialingResultsStats
              WHERE Type = 3
              GROUP BY Rep) a
        WHERE #AgentSummary.Rep = a.Rep;

        UPDATE #AgentSummary
        SET ThresholdClosingPct = CAST(ThresholdSales as numeric(18,2)) / CAST(ThresholdCalls as numeric(18,2)) * 100
        WHERE ThresholdCalls > 0;

        SELECT * FROM #AgentSummary WHERE Hours > 0";

        // Check params
        if (!empty($this->params['orderby']) && is_array($this->params['orderby'])) {
            $sort = '';
            foreach ($this->params['orderby'] as $col => $dir) {
                $sort .= ",$col $dir";
            }
            $sql .= ' ORDER BY ' . substr($sort, 1);
        } else {
            $sql .= ' ORDER BY Rep';
        }

        if (!$all) {
            $offset = ($this->params['curpage'] - 1) * $this->params['pagesize'];
            $sql .= " OFFSET $offset ROWS FETCH NEXT " . $this->params['pagesize'] . " ROWS ONLY";
        }

        return [$sql, $bind];
    }

    private function processResults($results)
    {
        // this sets the order of the columns
        foreach ($this->params['columns'] as $k => $v) {
            $total[$k] = '';
        }

        $total['Rep'] = 'Total:';
        $total['TalkTimeSec'] = 0;
        $total['TalkTimeCount'] = 0;
        $total['PausedTimeSec'] = 0;
        $total['WaitTimeSec'] = 0;
        $total['WaitTimeCount'] = 0;
        $total['DispositionTimeSec'] = 0;
        $total['DispositionTimeCount'] = 0;
        $total['LoggedInTime'] = 0;
        $total['Calls'] = 0;
        $total['Connects'] = 0;
        $total['Contacts'] = 0;
        $total['Hours'] = 0;
        $total['Leads'] = 0;
        $total['ThresholdCalls'] = 0;
        $total['ThresholdRatio'] = 0;
        $total['ThresholdSales'] = 0;
        $total['ThresholdClosingPct'] = 0;

        foreach ($results as &$rec) {
            $total['TalkTimeSec'] += $rec['TalkTimeSec'];
            $total['TalkTimeCount'] += $rec['TalkTimeCount'];
            $total['PausedTimeSec'] += $rec['PausedTimeSec'];
            $total['WaitTimeSec'] += $rec['WaitTimeSec'];
            $total['WaitTimeCount'] += $rec['WaitTimeCount'];
            $total['DispositionTimeSec'] += $rec['DispositionTimeSec'];
            $total['DispositionTimeCount'] += $rec['DispositionTimeCount'];
            $total['LoggedInTime'] += $rec['LoggedInTime'];
            $total['Calls'] += $rec['Calls'];
            $total['Connects'] += $rec['Connects'];
            $total['Contacts'] += $rec['Contacts'];
            $total['Hours'] += $rec['Hours'];
            $total['Leads'] += $rec['Leads'];
            $total['ThresholdCalls'] += $rec['ThresholdCalls'];
            $total['ThresholdSales'] += $rec['ThresholdSales'];

            $rec = $this->processRow($rec);
        }

        // calc total avgs
        $total['AvTalkTime'] = $total['TalkTimeCount'] == 0 ? 0 : round($total['TalkTimeSec'] / $total['TalkTimeCount']);
        $total['AvWaitTime'] = $total['WaitTimeCount'] == 0 ? 0 : round($total['WaitTimeSec'] / $total['WaitTimeCount']);
        $total['AvDispoTime'] = $total['DispositionTimeCount'] == 0 ? 0 : round($total['DispositionTimeSec'] / $total['DispositionTimeCount']);

        $total['CPH'] = $total['Hours'] == 0 ? 0 : number_format($total['Connects'] / $total['Hours'], 2);
        $total['APH'] = $total['Hours'] == 0 ? 0 : number_format($total['Leads'] / $total['Hours'], 2);
        $total['ConversionRate'] = $total['Contacts'] == 0 ? 0 : number_format($total['Leads'] / $total['Contacts'] * 100, 2) . '%';
        $total['ConversionFactor'] = ($total['Contacts'] == 0 || $total['Hours'] == 0) ? 0 : number_format($total['Leads'] / $total['Contacts'] / $total['Hours'], 2) . '%';

        $total['ThresholdRatio'] = number_format($total['Connects'] == 0 ? 0 : $total['ThresholdCalls'] / $total['Connects'] * 100, 2) . '%';
        $total['ThresholdClosingPct'] = number_format($total['ThresholdCalls'] == 0 ? 0 : $total['ThresholdSales'] / $total['ThresholdCalls'] * 100, 2) . '%';

        // remove count cols
        unset($total['TalkTimeCount']);
        unset($total['WaitTimeCount']);
        unset($total['DispositionTimeCount']);
        unset($total['ThresholdSales']);

        // format totals
        $total['TalkTimeSec'] = $this->secondsToHms($total['TalkTimeSec']);
        $total['AvTalkTime'] = $this->secondsToHms($total['AvTalkTime']);
        $total['PausedTimeSec'] = $this->secondsToHms($total['PausedTimeSec']);
        $total['WaitTimeSec'] = $this->secondsToHms($total['WaitTimeSec']);
        $total['AvWaitTime'] = $this->secondsToHms($total['AvWaitTime']);
        $total['DispositionTimeSec'] = $this->secondsToHms($total['DispositionTimeSec']);
        $total['AvDispoTime'] = $this->secondsToHms($total['AvDispoTime']);
        $total['LoggedInTime'] = $this->secondsToHms($total['LoggedInTime']);

        // Tack on the totals row
        $results[] = $total;

        return $results;
    }

    public function processRow($rec)
    {
        // remove count cols
        unset($rec['TalkTimeCount']);
        unset($rec['WaitTimeCount']);
        unset($rec['DispositionTimeCount']);
        unset($rec['ThresholdSales']);

        $rec['TalkTimeSec'] = $this->secondsToHms($rec['TalkTimeSec']);
        $rec['AvTalkTime'] = $this->secondsToHms($rec['AvTalkTime']);
        $rec['PausedTimeSec'] = $this->secondsToHms($rec['PausedTimeSec']);
        $rec['WaitTimeSec'] = $this->secondsToHms($rec['WaitTimeSec']);
        $rec['AvWaitTime'] = $this->secondsToHms($rec['AvWaitTime']);
        $rec['DispositionTimeSec'] = $this->secondsToHms($rec['DispositionTimeSec']);
        $rec['AvDispoTime'] = $this->secondsToHms($rec['AvDispoTime']);
        $rec['LoggedInTime'] = $this->secondsToHms($rec['LoggedInTime']);

        $rec['ConversionRate'] .= '%';
        $rec['ConversionFactor'] .= '%';
        $rec['ThresholdRatio'] .= '%';
        $rec['ThresholdClosingPct'] .= '%';

        return $rec;
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
