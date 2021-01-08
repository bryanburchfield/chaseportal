<?php

namespace App\Services\Reports;

use App\Traits\CampaignTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\ReportTraits;

class AgentSummarySubcampaign
{
    use ReportTraits;
    use CampaignTraits;

    public function __construct()
    {
        $this->initilaizeParams();

        $this->params['reportName'] = 'reports.agent_summary_subcampaign';
        $this->params['campaigns'] = [];
        $this->params['reps'] = [];
        $this->params['skills'] = [];
        $this->params['campaigns'] = [];
        $this->params['hasTotals'] = true;
        $this->params['columns'] = [
            'Campaign' => 'reports.campaign',
            'Subcampaign' => 'reports.subcampaign',
            'Rep' => 'reports.rep',
            'Dialed' => 'reports.dialed',
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
            'loggedintime' => 'reports.loggedintime',
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
            $this->params['totrows'] = count($results);
            $this->params['totpages'] = floor($this->params['totrows'] / $this->params['pagesize']);
            $this->params['totpages'] += floor($this->params['totrows'] / $this->params['pagesize']) == ($this->params['totrows'] / $this->params['pagesize']) ? 0 : 1;
            $results = $this->processResults($results);
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
        $campaigns = str_replace("'", "''", implode('!#!', $this->params['campaigns']));
        $skills = str_replace("'", "''", implode('!#!', $this->params['skills']));

        $bind['reps'] = $reps;
        $bind['campaigns'] = $campaigns;

        $sql = "SET NOCOUNT ON;";

        if (!empty($this->params['skills'])) {
            $bind['skills'] = $skills;
            $sql .= "
            CREATE TABLE #SelectedSkill(SkillName varchar(50) Primary Key);
            INSERT INTO #SelectedSkill SELECT DISTINCT [value] from dbo.SPLIT(:skills, '!#!');";
        }

        $sql .= "
        CREATE TABLE #AgentSummary(
            Campaign varchar(50),
            Subcampaign varchar(50),
            Rep varchar(50) COLLATE SQL_Latin1_General_CP1_CS_AS NOT NULL,
            Dialed int DEFAULT 0,
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
            loggedintime int DEFAULT 0
            );

        CREATE TABLE #SelectedRep(Rep varchar(50) Primary Key);
        INSERT #SelectedRep(Rep)
        SELECT DISTINCT [value] from dbo.SPLIT(:reps, '!#!');

        CREATE TABLE #SelectedCampaign(CampaignName varchar(50) Primary Key);
        INSERT INTO #SelectedCampaign
        SELECT DISTINCT [value] from dbo.SPLIT(:campaigns, '!#!');

        SELECT * INTO #DialingResultsStats FROM (";

        $union = '';
        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id' . $i] =  Auth::user()->group_id;
            $bind['startdate' . $i] = $startDate;
            $bind['enddate' . $i] = $endDate;

            $sql .= " $union SELECT Campaign, Subcampaign, Rep, [Type], COUNT(id) as [Count]
            FROM
            (SELECT r.Campaign, r.Subcampaign, r.Rep,
                    IsNull(DI.Type,0) as [Type], r.id
                FROM [$db].[dbo].[DialingResults] r WITH(NOLOCK)
                LEFT JOIN [$db].[dbo].[Dispos] DI ON DI.id = r.DispositionId";

            if (!empty($reps)) {
                $sql .= " INNER JOIN #SelectedRep sr on sr.Rep COLLATE SQL_Latin1_General_CP1_CS_AS = r.Rep";
            }

            if (!empty($this->params['skills'])) {
                $sql .= "
                INNER JOIN [$db].[dbo].[Reps] RR on RR.RepName COLLATE SQL_Latin1_General_CP1_CS_AS = r.Rep
                INNER JOIN #SelectedSkill SS on SS.SkillName COLLATE SQL_Latin1_General_CP1_CS_AS = RR.Skill";
            }

            $sql .= " INNER JOIN #SelectedCampaign sc ON sc.CampaignName = r.Campaign
                WHERE r.GroupId = :group_id$i
                AND r.CallDate >= :startdate$i
                AND r.CallDate < :enddate$i
                AND r.CallStatus NOT IN ('Inbound', 'CR_CNCT/CON_CAD', 'CR_CNCT/CON_PVD')
                AND r.Duration > 0";

            if (session('ssoRelativeReps', 0)) {
                $sql .= " AND r.Rep IN (SELECT RepName FROM dbo.GetAllRelativeReps(:ssouserrep1$i))";
                $bind['ssouserrep1' . $i] = session('ssoUsername');
            }

            $sql .= "
            ) a
            GROUP BY Campaign, Subcampaign, Rep, [Type]";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp;

        CREATE INDEX IX_Type ON #DialingResultsStats ([Type]);

        SELECT * INTO #AgentSummaryDuration FROM (";

        $union = '';
        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id1' . $i] =  Auth::user()->group_id;
            $bind['startdate1' . $i] = $startDate;
            $bind['enddate1' . $i] = $endDate;

            $sql .= " $union SELECT aa.Campaign, aa.Subcampaign, aa.Rep, [Action], SUM(Duration) as Duration, COUNT(aa.id) as [Count]
            FROM [$db].[dbo].[AgentActivity] as aa WITH(NOLOCK)";

            if (!empty($reps)) {
                $sql .= " INNER JOIN #SelectedRep sr on sr.Rep COLLATE SQL_Latin1_General_CP1_CS_AS = aa.Rep";
            }

            if (!empty($this->params['skills'])) {
                $sql .= "
                INNER JOIN [$db].[dbo].[Reps] RR on RR.RepName COLLATE SQL_Latin1_General_CP1_CS_AS = aa.Rep
                INNER JOIN #SelectedSkill SS on SS.SkillName COLLATE SQL_Latin1_General_CP1_CS_AS = RR.Skill";
            }

            $sql .= " INNER JOIN #SelectedCampaign c on c.CampaignName = aa.Campaign
            WHERE aa.GroupId = :group_id1$i
            AND aa.Date >= :startdate1$i
            AND aa.Date < :enddate1$i
            AND aa.Duration > 0";

            if (session('ssoRelativeReps', 0)) {
                $sql .= " AND aa.Rep IN (SELECT RepName FROM dbo.GetAllRelativeReps(:ssouserrep2$i))";
                $bind['ssouserrep2' . $i] = session('ssoUsername');
            }

            $sql .= "
            GROUP BY aa.Campaign, aa.Subcampaign, aa.Rep, [Action]";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp;

        CREATE INDEX IX_Rep ON #AgentSummaryDuration (Campaign, Subcampaign, Rep);

        INSERT INTO #AgentSummary (Campaign, Subcampaign, Rep, Dialed)
        SELECT Campaign, Subcampaign, Rep, SUM([Count])
        FROM #DialingResultsStats
        GROUP BY Campaign, Subcampaign, Rep

        INSERT INTO #AgentSummary (Campaign, Subcampaign, Rep, Connects)
        SELECT Campaign, Subcampaign, Rep, SUM([Count])
        FROM #DialingResultsStats
        WHERE [Type] > 0
        GROUP BY Campaign, Subcampaign, Rep

        INSERT INTO #AgentSummary (Campaign, Subcampaign, Rep, Contacts)
        SELECT Campaign, Subcampaign, Rep, SUM([Count])
        FROM #DialingResultsStats
        WHERE [Type] > 1
        GROUP BY Campaign, Subcampaign, Rep

        INSERT INTO #AgentSummary (Campaign, Subcampaign, Rep, Hours)
        SELECT aa.Campaign, aa.Subcampaign, aa.Rep, IsNull(SUM(Duration)/3600,0)
        FROM #AgentSummaryDuration aa
        WHERE aa.Action <> 'Paused'
        GROUP BY aa.Campaign, aa.Subcampaign, aa.Rep

        INSERT INTO #AgentSummary (Campaign, Subcampaign, Rep, Leads)
        SELECT Campaign, Subcampaign, Rep, SUM([Count])
        FROM #DialingResultsStats
        WHERE [Type] = 3
        GROUP BY Campaign, Subcampaign, Rep

        INSERT INTO #AgentSummary (Campaign, Subcampaign, Rep, TalkTimeSec, TalkTimeCount)
        SELECT aa.Campaign, aa.Subcampaign, aa.Rep, SUM(Duration), SUM([Count])
        FROM #AgentSummaryDuration aa WITH(NOLOCK)
        WHERE aa.Action in ('Call', 'ManualCall', 'InboundCall')
        GROUP BY aa.Campaign, aa.Subcampaign, aa.Rep

        INSERT INTO #AgentSummary (Campaign, Subcampaign, Rep, PausedTimeSec)
        SELECT aa.Campaign, aa.Subcampaign, aa.Rep, SUM(Duration)
        FROM #AgentSummaryDuration aa
        WHERE aa.Action = 'Paused'
        GROUP BY aa.Campaign, aa.Subcampaign, aa.Rep

        INSERT INTO #AgentSummary (Campaign, Subcampaign, Rep, WaitTimeSec, WaitTimeCount)
        SELECT aa.Campaign, aa.Subcampaign, aa.Rep, SUM(Duration), SUM([Count])
        FROM #AgentSummaryDuration aa WITH(NOLOCK)
        WHERE aa.Action = 'Waiting'
        GROUP BY aa.Campaign, aa.Subcampaign, aa.Rep

        INSERT INTO #AgentSummary (Campaign, Subcampaign, Rep, DispositionTimeSec, DispositionTimeCount)
        SELECT aa.Campaign, aa.Subcampaign, aa.Rep, SUM(Duration), SUM([Count])
        FROM #AgentSummaryDuration aa WITH(NOLOCK)
        WHERE aa.Action = 'Disposition'
        GROUP BY aa.Campaign, aa.Subcampaign, aa.Rep

        INSERT INTO #AgentSummary (Campaign, Subcampaign, Rep, loggedintime)
        SELECT aa.Campaign, aa.Subcampaign, aa.Rep, SUM(Duration)
        FROM #AgentSummaryDuration aa
        GROUP BY aa.Campaign, aa.Subcampaign, aa.Rep

        SELECT Campaign, Subcampaign, Rep,
        SUM(Dialed) AS Dialed,
        SUM(Connects) AS Connects,
        SUM(Contacts) AS Contacts,
        SUM(Hours) AS Hours,
        SUM(Leads) AS Leads,
        SUM(CPH) AS CPH,
        SUM(APH) AS APH,
        SUM(ConversionRate) AS ConversionRate,
        SUM(ConversionFactor) AS ConversionFactor,
        SUM(TalkTimeSec) AS TalkTimeSec,
        SUM(TalkTimeCount) AS TalkTimeCount,
        SUM(AvTalkTime) AS AvTalkTime,
        SUM(PausedTimeSec) AS PausedTimeSec,
        SUM(WaitTimeSec) AS WaitTimeSec,
        SUM(WaitTimeCount) AS WaitTimeCount,
        SUM(AvWaitTime) AS AvWaitTime,
        SUM(DispositionTimeSec) AS DispositionTimeSec,
        SUM(DispositionTimeCount) AS DispositionTimeCount,
        SUM(AvDispoTime) AS AvDispoTime,
        SUM(loggedintime) AS loggedintime
        INTO #Final
        FROM #AgentSummary
        GROUP BY Campaign, Subcampaign, Rep
        ORDER BY Campaign, Subcampaign, Rep

        UPDATE #Final
        SET CPH = Connects/Hours,
            APH = Leads/Hours
        WHERE Hours > 0;

        UPDATE #Final
        SET ConversionRate = (CAST(Leads as numeric(18,2)) / CAST(Contacts as numeric(18,2))) * 100
        WHERE Contacts > 0;

        UPDATE #Final
        SET ConversionFactor = (CAST(Leads as numeric(18,2)) /CAST(Contacts as numeric(18,2))) / Hours
        WHERE Hours > 0 AND Contacts > 0;

        UPDATE #Final
        SET AvWaitTime = WaitTimeSec / WaitTimeCount
        WHERE WaitTimeCount > 0;

        UPDATE #Final
        SET AvTalkTime = TalkTimeSec / TalkTimeCount
        WHERE TalkTimeCount > 0;

        UPDATE #Final
        SET AvDispoTime = DispositionTimeSec / DispositionTimeCount
        WHERE DispositionTimeCount > 0;

        SELECT
         Campaign,
         Subcampaign,
         Rep,
         Dialed,
         Connects,
         Contacts,
         Hours,
         Leads,
         CPH,
         APH,
         ConversionRate,
         ConversionFactor,
         TalkTimeSec,
         AvTalkTime,
         PausedTimeSec,
         WaitTimeSec,
         AvWaitTime,
         DispositionTimeSec,
         AvDispoTime,
         loggedintime,
         TalkTimeCount,
         WaitTimeCount,
         DispositionTimeCount
        FROM #Final WHERE Hours > 0";

        // Check params
        if (!empty($this->params['orderby']) && is_array($this->params['orderby'])) {
            $sort = '';
            foreach ($this->params['orderby'] as $col => $dir) {
                $sort .= ",$col $dir";
            }
            $sql .= ' ORDER BY ' . substr($sort, 1);
        } else {
            $sql .= ' ORDER BY Rep, Campaign, Subcampaign';
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

        $total['Campaign'] = 'Total:';
        $total['Dialed'] = 0;
        $total['Contacts'] = 0;
        $total['Connects'] = 0;
        $total['Hours'] = 0;
        $total['Leads'] = 0;
        $total['CPH'] = 0;
        $total['APH'] = 0;
        $total['ConversionRate'] = 0;
        $total['ConversionFactor'] = 0;
        $total['TalkTimeSec'] = 0;
        $total['AvTalkTime'] = 0;
        $total['PausedTimeSec'] = 0;
        $total['WaitTimeSec'] = 0;
        $total['AvWaitTime'] = 0;
        $total['DispositionTimeSec'] = 0;
        $total['AvDispoTime'] = 0;
        $total['loggedintime'] = 0;

        $total['TalkTimeCount'] = 0;
        $total['WaitTimeCount'] = 0;
        $total['DispositionTimeCount'] = 0;

        foreach ($results as &$rec) {
            $total['Dialed'] += $rec['Dialed'];
            $total['Contacts'] += $rec['Contacts'];
            $total['Connects'] += $rec['Connects'];
            $total['Hours'] += $rec['Hours'];
            $total['Leads'] += $rec['Leads'];
            $total['TalkTimeSec'] += $rec['TalkTimeSec'];
            $total['PausedTimeSec'] += $rec['PausedTimeSec'];
            $total['WaitTimeSec'] += $rec['WaitTimeSec'];
            $total['DispositionTimeSec'] += $rec['DispositionTimeSec'];
            $total['loggedintime'] += $rec['loggedintime'];

            $total['TalkTimeCount'] += $rec['TalkTimeCount'];
            $total['WaitTimeCount'] += $rec['WaitTimeCount'];
            $total['DispositionTimeCount'] += $rec['DispositionTimeCount'];

            // remove count cols
            unset($rec['TalkTimeCount']);
            unset($rec['WaitTimeCount']);
            unset($rec['DispositionTimeCount']);

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

        // remove count cols
        unset($total['TalkTimeCount']);
        unset($total['WaitTimeCount']);
        unset($total['DispositionTimeCount']);

        // format totals
        $total['TalkTimeSec'] = $this->secondsToHms($total['TalkTimeSec']);
        $total['AvTalkTime'] = $this->secondsToHms($total['AvTalkTime']);
        $total['PausedTimeSec'] = $this->secondsToHms($total['PausedTimeSec']);
        $total['WaitTimeSec'] = $this->secondsToHms($total['WaitTimeSec']);
        $total['AvWaitTime'] = $this->secondsToHms($total['AvWaitTime']);
        $total['DispositionTimeSec'] = $this->secondsToHms($total['DispositionTimeSec']);
        $total['AvDispoTime'] = $this->secondsToHms($total['AvDispoTime']);
        $total['loggedintime'] = $this->secondsToHms($total['loggedintime']);

        // Tack on the totals row
        $results[] = $total;

        return $results;
    }

    public function processRow($rec)
    {
        $rec['TalkTimeSec'] = $this->secondsToHms($rec['TalkTimeSec']);
        $rec['AvTalkTime'] = $this->secondsToHms($rec['AvTalkTime']);
        $rec['PausedTimeSec'] = $this->secondsToHms($rec['PausedTimeSec']);
        $rec['WaitTimeSec'] = $this->secondsToHms($rec['WaitTimeSec']);
        $rec['AvWaitTime'] = $this->secondsToHms($rec['AvWaitTime']);
        $rec['DispositionTimeSec'] = $this->secondsToHms($rec['DispositionTimeSec']);
        $rec['AvDispoTime'] = $this->secondsToHms($rec['AvDispoTime']);
        $rec['loggedintime'] = $this->secondsToHms($rec['loggedintime']);

        $rec['ConversionRate'] .= '%';
        $rec['ConversionFactor'] .= '%';

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

        if (empty($request->campaigns)) {
            $this->errors->add('campaigns.required', trans('reports.errcampaignsrequired'));
        } else {
            $this->params['campaigns'] = $request->campaigns;
        }

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
