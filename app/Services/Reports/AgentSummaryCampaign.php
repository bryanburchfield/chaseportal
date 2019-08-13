<?php

namespace App\Services\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\ReportTraits;

class AgentSummaryCampaign
{
    use ReportTraits;

    public function __construct()
    {
        $this->initilaizeParams();

        $this->params['fromdate'] = date("m/d/Y 9:00 \A\M");
        $this->params['todate'] = date("m/d/Y 8:00 \P\M");
        $this->params['reps'] = [];
        $this->params['skills'] = [];
        $this->params['campaigns'] = [];
        $this->params['hasTotals'] = true;
        $this->params['columns'] = [
            'Rep' => 'Rep',
            'Contacts' => 'Contacts',
            'Connects' => 'Connects',
            'Hours' => 'Hours Worked',
            'Leads' => 'Sale/Lead/App',
            'CPH' => 'Connects per hr',
            'APH' => 'S-L-A/HR',
            'ConversionRate' => 'Conversion Rate',
            'ConversionFactor' => 'Conversion Factor',
            'TalkTimeSec' => 'Talk Time',
            'AvTalkTime' => 'Avg Talk Time',
            'PausedTimeSec' => 'Break Time',
            'WaitTimeSec' => 'Wait Time',
            'AvWaitTime' => 'Avg Wait Time',
            'DispositionTimeSec' => 'Wrap Up Time',
            'AvDispoTime' => 'Avg Wrap Up Time',
            'ConnectedTimeSec' => 'Logged In Time',
        ];
    }

    public function getFilters()
    {
        $filters = [
            'reps' => $this->getAllReps(),
            'skills' => $this->getAllSkills(),
            'campaigns' => $this->getAllCampaigns(),
        ];

        return $filters;
    }

    private function executeReport($all = false)
    {
        // Log::debug($this->params);
        list($fromDate, $toDate) = $this->dateRange($this->params['fromdate'], $this->params['todate']);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');
        $reps = str_replace("'", "''", implode('!#!', $this->params['reps']));
        $campaigns = str_replace("'", "''", implode('!#!', $this->params['campaigns']));

        $bind['group_id1'] =  Auth::user()->group_id;
        $bind['group_id2'] =  Auth::user()->group_id;
        $bind['group_id3'] =  Auth::user()->group_id;
        $bind['startdate1'] = $startDate;
        $bind['startdate2'] = $startDate;
        $bind['enddate1'] = $endDate;
        $bind['enddate2'] = $endDate;
        $bind['reps'] = $reps;
        $bind['campaigns'] = $campaigns;

        $sql = "SET NOCOUNT ON;";

        if (!empty($this->params['skills'])) {
            $list = str_replace("'", "''", implode('!#!', $this->params['skills']));
            $sql .= "
            CREATE TABLE #SelectedSkill(SkillName varchar(50) Primary Key);
            INSERT INTO #SelectedSkill SELECT DISTINCT [value] from dbo.SPLIT('$list', '!#!');";
        }

        $sql .= "
                CREATE TABLE #AgentSummary(
                    Rep varchar(50) COLLATE SQL_Latin1_General_CP1_CS_AS NOT NULL,
                    Contacts int DEFAULT 0,
                    Connects int DEFAULT 0,
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
                    ConnectedTimeSec int DEFAULT 0
                    );

                    INSERT #AgentSummary(Rep)
                    SELECT DISTINCT Rep FROM (";

        $union = '';
        foreach (Auth::user()->getDatabaseArray() as $db) {
            $sql .= "
                        $union SELECT [value] Rep from dbo.SPLIT(:reps, '!#!')";

            if (!empty($this->params['skills'])) {
                $sql .= "
                            INNER JOIN [$db].[dbo].[Reps] RR on RR.RepName COLLATE SQL_Latin1_General_CP1_CS_AS = [value]
                            INNER JOIN #SelectedSkill SS on SS.SkillName COLLATE SQL_Latin1_General_CP1_CS_AS = RR.Skill";
            }
            $union = "UNION";
        }

        $sql .= ") tmp;

                CREATE TABLE #SelectedCampaign(CampaignName varchar(50) Primary Key);
                INSERT INTO #SelectedCampaign
                SELECT DISTINCT [value] from dbo.SPLIT(:campaigns, '!#!');

                SELECT * INTO #DialingResultsStats FROM (";

        $union = '';
        foreach (Auth::user()->getDatabaseArray() as $db) {
            $sql .= " $union SELECT Rep, [Type], COUNT(id) as [Count]
                    FROM
                    (SELECT r.Rep,
                            IsNull((SELECT TOP 1 [Type]
                            FROM [$db].[dbo].[Dispos]
                            WHERE Disposition=r.CallStatus AND (GroupId=:group_id1 OR IsSystem=1) AND (Campaign=r.Campaign OR Campaign='') ORDER BY [Description] Desc), 0) as [Type],
                            r.id
                        FROM [$db].[dbo].[DialingResults] r WITH(NOLOCK)
                        INNER JOIN #AgentSummary sr on sr.Rep COLLATE SQL_Latin1_General_CP1_CS_AS = r.Rep
                        INNER JOIN #SelectedCampaign sc ON sc.CampaignName = r.Campaign
                        WHERE r.GroupId = :group_id2
                        AND r.Date >= :startdate1
                        AND r.Date < :enddate1
                    ) a
                    WHERE [Type] > 0
                    GROUP BY Rep, [Type]";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp;

                CREATE INDEX IX_RepType ON #DialingResultsStats (Rep, [Type]);
                CREATE INDEX IX_Type ON #DialingResultsStats ([Type]);

                SELECT * INTO #AgentSummaryDuration FROM (";

        $union = '';
        foreach (Auth::user()->getDatabaseArray() as $db) {
            $sql .= " $union SELECT aa.Rep, [Action], SUM(Duration) as Duration, COUNT(id) as [Count]
                    FROM [$db].[dbo].[AgentActivity] as aa WITH(NOLOCK)
                    INNER JOIN #AgentSummary r on r.Rep COLLATE SQL_Latin1_General_CP1_CS_AS = aa.Rep
                    INNER JOIN #SelectedCampaign c on c.CampaignName = aa.Campaign
                    WHERE GroupId = :group_id3
                    AND Date >= :startdate2
                    AND Date < :enddate2
                    AND Duration > 0
                    GROUP BY aa.Rep, [Action]";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp;

                CREATE INDEX IX_Rep ON #AgentSummaryDuration (Rep);
                CREATE INDEX IX_RepDuration ON #AgentSummaryDuration (Rep, Duration);
                CREATE INDEX IX_Action ON #AgentSummaryDuration ([Action]);
                CREATE INDEX IX_RepAction ON #AgentSummaryDuration (Rep, Duration, [Action]);

                UPDATE #AgentSummary
                SET Contacts = a.Contacts
                FROM (SELECT Rep, SUM([Count]) as Contacts
                      FROM #DialingResultsStats
                      WHERE [Type] > 1
                      GROUP BY Rep) a
                WHERE #AgentSummary.Rep = a.Rep;

                UPDATE #AgentSummary
                SET Connects = a.Connects
                FROM (SELECT Rep, SUM([Count]) as Connects
                      FROM #DialingResultsStats
                      WHERE [Type] > 0
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
                FROM (SELECT aa.Rep, SUM(Duration) as TalkTime, COUNT(*) as tot
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
                FROM (SELECT aa.Rep, SUM(Duration) as WaitTime, COUNT(*) as tot
                      FROM #AgentSummaryDuration aa WITH(NOLOCK)
                      WHERE aa.Action = 'Waiting'
                      GROUP BY aa.Rep) a
                WHERE #AgentSummary.Rep = a.Rep;

                UPDATE #AgentSummary
                SET DispositionTimeSec = a.DispositionTime,
                    DispositionTimeCount = a.tot
                FROM (SELECT aa.Rep, SUM(Duration) as DispositionTime, COUNT(*) as tot
                      FROM #AgentSummaryDuration aa WITH(NOLOCK)
                      WHERE aa.Action = 'Disposition'
                      GROUP BY aa.Rep) a
                WHERE #AgentSummary.Rep = a.Rep;

                UPDATE #AgentSummary
                SET ConnectedTimeSec = a.Hours
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

        $results = $this->runSql($sql, $bind);

        if (empty($results)) {
            $this->params['totrows'] = 0;
            $this->params['totrows'] = 0;
            $this->params['totpages'] = 1;
            $this->params['curpage'] = 1;
        } else {
            $this->params['totrows'] = count($results);
            $this->params['totpages'] = floor($this->params['totrows'] / $this->params['pagesize']);
            $this->params['totpages'] += floor($this->params['totrows'] / $this->params['pagesize']) == ($this->params['totrows'] / $this->params['pagesize']) ? 0 : 1;
        }

        $results = $this->processResults($results);
        return $this->getPage($results);
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
        $total['ConnectedTimeSec'] = 0;
        $total['Contacts'] = 0;
        $total['Connects'] = 0;
        $total['Hours'] = 0;
        $total['Leads'] = 0;

        foreach ($results as &$rec) {
            $total['TalkTimeSec'] += $rec['TalkTimeSec'];
            $total['TalkTimeCount'] += $rec['TalkTimeCount'];
            $total['PausedTimeSec'] += $rec['PausedTimeSec'];
            $total['WaitTimeSec'] += $rec['WaitTimeSec'];
            $total['WaitTimeCount'] += $rec['WaitTimeCount'];
            $total['DispositionTimeSec'] += $rec['DispositionTimeSec'];
            $total['DispositionTimeCount'] += $rec['DispositionTimeCount'];
            $total['ConnectedTimeSec'] += $rec['ConnectedTimeSec'];
            $total['Contacts'] += $rec['Contacts'];
            $total['Connects'] += $rec['Connects'];
            $total['Hours'] += $rec['Hours'];
            $total['Leads'] += $rec['Leads'];

            // remove count cols
            unset($rec['TalkTimeCount']);
            unset($rec['WaitTimeCount']);
            unset($rec['DispositionTimeCount']);

            $rec['TalkTimeSec'] = secondsToHms($rec['TalkTimeSec']);
            $rec['AvTalkTime'] = secondsToHms($rec['AvTalkTime']);
            $rec['PausedTimeSec'] = secondsToHms($rec['PausedTimeSec']);
            $rec['WaitTimeSec'] = secondsToHms($rec['WaitTimeSec']);
            $rec['AvWaitTime'] = secondsToHms($rec['AvWaitTime']);
            $rec['DispositionTimeSec'] = secondsToHms($rec['DispositionTimeSec']);
            $rec['AvDispoTime'] = secondsToHms($rec['AvDispoTime']);
            $rec['ConnectedTimeSec'] = secondsToHms($rec['ConnectedTimeSec']);

            $rec['ConversionRate'] .= '%';
            $rec['ConversionFactor'] .= '%';
        }

        // calc total avgs
        $total['AvTalkTime'] = $total['TalkTimeCount'] == 0 ? 0 : round($total['TalkTimeSec'] / $total['TalkTimeCount']);
        $total['AvWaitTime'] = $total['WaitTimeCount'] == 0 ? 0 : round($total['WaitTimeSec'] / $total['WaitTimeCount']);
        $total['AvDispoTime'] = $total['DispositionTimeCount'] == 0 ? 0 : round($total['DispositionTimeSec'] / $total['DispositionTimeCount']);

        $total['CPH'] = $total['Hours'] == 0 ? 0 : number_format($total['Connects'] / $total['Hours'], 2);
        $total['APH'] = $total['Hours'] == 0 ? 0 : number_format($total['Leads'] / $total['Hours'], 2);
        $total['ConversionRate'] = $total['Contacts'] == 0 ? 0 : number_format($total['Leads'] / $total['Contacts'] * 100, 2) . '%';
        $total['ConversionFactor'] = ($total['Contacts'] == 0 || $total['Hours'] == 0) ? 0 : number_format($total['Leads'] / $total['Contacts'] / $total['Hours'], 2) . '%';

        // remove count cols
        unset($total['TalkTimeCount']);
        unset($total['WaitTimeCount']);
        unset($total['DispositionTimeCount']);

        // format totals
        $total['TalkTimeSec'] = secondsToHms($total['TalkTimeSec']);
        $total['AvTalkTime'] = secondsToHms($total['AvTalkTime']);
        $total['PausedTimeSec'] = secondsToHms($total['PausedTimeSec']);
        $total['WaitTimeSec'] = secondsToHms($total['WaitTimeSec']);
        $total['AvWaitTime'] = secondsToHms($total['AvWaitTime']);
        $total['DispositionTimeSec'] = secondsToHms($total['DispositionTimeSec']);
        $total['AvDispoTime'] = secondsToHms($total['AvDispoTime']);
        $total['ConnectedTimeSec'] = secondsToHms($total['ConnectedTimeSec']);

        // Tack on the totals row
        $results[] = $total;

        return $results;
    }

    private function processInput(Request $request)
    {
        // Check page filters
        $this->checkPageFilters($request);

        // Check report filters
        $this->checkDateRangeFilters($request);

        if (empty($request->campaigns)) {
            $this->errors->add('campaigns.required', "At least 1 Campaign required");
        } else {
            $this->params['campaigns'] = $request->campaigns;
        }

        if (empty($request->reps)) {
            $this->errors->add('reps.required', "At least 1 Rep required");
        } else {
            $this->params['reps'] = $request->reps;
        }

        if (!empty($request->skills)) {
            $this->params['skills'] = $request->skills;
        }

        return $this->errors;
    }
}
