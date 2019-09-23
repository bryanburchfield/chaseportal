<?php

namespace App\Services\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\ReportTraits;

class AgentAnalysis
{
    use ReportTraits;

    public function __construct()
    {
        $this->initilaizeParams();

        $this->params['reportName'] = 'Agent Analysis Report';
        $this->params['fromdate'] = date("m/d/Y 9:00 \A\M");
        $this->params['todate'] = date("m/d/Y 8:00 \P\M");
        $this->params['skills'] = [];
        $this->params['columns'] = [
            'Date' => 'Date',
            'Rep' => 'Rep',
            'Campaign' => 'Campaign',
            'Hours' => 'Hours Worked',
            'Contacts' => 'Contacts',
            'Connects' => 'Connects',
            'CPH' => 'CPH',
            'ConversionRate' => 'Conversion Rate',
            'ConversionFactor' => 'Conversion Factor',
            'Leads' => 'Sale/Lead/App',
            'APH' => 'S-L-A/HR',
            'CallBacks' => 'Call Backs',
            'AvTalkTime' => 'Avg Talk Time',
            'AvWaitTime' => 'Avg Wait Time',
            'AvailTimeSec' => 'Time Avail',
            'PausedTimeSec' => 'Time Paused',
            'ConnectedTimeSec' => 'Talk Time',
            'DispositionTimeSec' => 'Wrap Up Time',
            'LoggedInTimeSec' => 'Logged In Time',
        ];
    }

    public function getFilters()
    {
        $filters = [
            'skills' => $this->getAllSkills(),
            'db_list' => Auth::user()->getDatabaseArray(),
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

        $tz =  Auth::user()->tz;

        $sql = "SET NOCOUNT ON;";

        if (!empty($this->params['skills'])) {
            $list = str_replace("'", "''", implode('!#!', $this->params['skills']));
            $sql .= "
            CREATE TABLE #SelectedSkill(SkillName varchar(50) Primary Key);
            INSERT INTO #SelectedSkill SELECT DISTINCT [value] from dbo.SPLIT('$list', '!#!');";
        }

        $sql .= "
        CREATE TABLE #AgentAnalysis(
            Date date,
            Rep varchar(50) COLLATE SQL_Latin1_General_CP1_CS_AS NOT NULL,
            Campaign varchar(50) NOT NULL,
            Hours numeric(18,2) DEFAULT 0,
            Contacts int DEFAULT 0,
            Connects int DEFAULT 0,
            CPH numeric(18,2) DEFAULT 0,
            ConversionRate numeric(18,2) DEFAULT 0,
            ConversionFactor numeric(18,2) DEFAULT 0,
            Leads int DEFAULT 0,
            APH numeric(18,2) DEFAULT 0,
            CallBacks int DEFAULT 0,
            AvTalkTime numeric(18,2) DEFAULT 0,
            AvWaitTime numeric(18,2) DEFAULT 0,
            AvailTimeSec numeric(18,3) DEFAULT 0,
            PausedTimeSec numeric(18,3) DEFAULT 0,
            ConnectedTimeSec numeric(18,3) DEFAULT 0,
            DispositionTimeSec numeric(18,3) DEFAULT 0,
            LoggedInTimeSec numeric(18,3) DEFAULT 0
        );

        SELECT * INTO #AgentActivityDuration FROM (";

        $union = '';
        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id1' . $i] = Auth::user()->group_id;
            $bind['startdate1' . $i] = $startDate;
            $bind['enddate1' . $i] = $endDate;

            $sql .= " $union SELECT
                CAST(CONVERT(datetimeoffset, AA.Date) AT TIME ZONE '$tz' as date) as Date,
                AA.Campaign,
                AA.Rep,
                [Action],
                SUM(AA.Duration) as Duration,
                COUNT(AA.id) as [Count]
            FROM [$db].[dbo].[AgentActivity] AA WITH(NOLOCK)";

            if (!empty($this->params['skills'])) {
                $sql .= "
                INNER JOIN [$db].[dbo].[Reps] RR on RR.RepName COLLATE SQL_Latin1_General_CP1_CS_AS = AA.Rep
                INNER JOIN #SelectedSkill SS on SS.SkillName COLLATE SQL_Latin1_General_CP1_CS_AS = RR.Skill";
            }

            $sql .= "
            WHERE AA.GroupId = :group_id1$i
            AND	AA.Date >= :startdate1$i
            AND AA.Date < :enddate1$i
            GROUP BY CAST(CONVERT(datetimeoffset, AA.Date) AT TIME ZONE '$tz' as date), Campaign, Rep, [Action]";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp;

        CREATE INDEX IX_CampaignRepAction ON #AgentActivityDuration (Campaign, Rep, [Action], Date);
        CREATE INDEX IX_Action ON #AgentActivityDuration ([Action]);

        INSERT #AgentAnalysis(Date, Campaign, Rep)
        SELECT DISTINCT
        Date,
        Campaign,
        Rep
        FROM #AgentActivityDuration;

        CREATE UNIQUE INDEX IX_CampaignRep ON #AgentAnalysis (Campaign, Rep, Date);

         SELECT * INTO #DialingResultsStats FROM (";

        $union = '';
        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id2' . $i] = Auth::user()->group_id;
            $bind['startdate2' . $i] = $startDate;
            $bind['enddate2' . $i] = $endDate;

            $sql .= " $union SELECT
                CAST(CONVERT(datetimeoffset, r.Date) AT TIME ZONE '$tz' as date) as Date,
                r.Campaign,
                r.Rep,
                d.Type,
                COUNT(r.id) as [Count]
            FROM [$db].[dbo].[DialingResults] r WITH(NOLOCK)";

            if (!empty($this->params['skills'])) {
                $sql .= "
                INNER JOIN [$db].[dbo].[Reps] RR on RR.RepName COLLATE SQL_Latin1_General_CP1_CS_AS = r.Rep
                INNER JOIN #SelectedSkill SS on SS.SkillName COLLATE SQL_Latin1_General_CP1_CS_AS = RR.Skill";
            }

            $sql .= "
            CROSS APPLY (SELECT TOP 1 [Type]
                        FROM [$db].[dbo].[Dispos]
                        WHERE Disposition=r.CallStatus
                        AND (GroupId=r.GroupId OR IsSystem=1)
                        AND (Campaign=r.Campaign OR Campaign='')
                        ORDER BY [Description] Desc) d
            WHERE r.GroupId = :group_id2$i
            AND r.Date >= :startdate2$i
            AND r.Date < :enddate2$i
            AND d.Type > 0
            GROUP BY CAST(CONVERT(datetimeoffset, r.Date) AT TIME ZONE '$tz' as date), r.Campaign, r.Rep, d.Type";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp;

        CREATE INDEX IX_CampaignRepType ON #DialingResultsStats (Campaign, Rep, [Type], Date);
        CREATE INDEX IX_Type ON #DialingResultsStats ([Type]);";

        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id3' . $i] = Auth::user()->group_id;
            $bind['startdate3' . $i] = $startDate;
            $bind['enddate3' . $i] = $endDate;

            $sql .= "UPDATE #AgentAnalysis
            SET CallBacks += a.CallBacks
            FROM (SELECT r.Rep, r.Campaign,
                    COUNT(r.id) as CallBacks,
                    CAST(CONVERT(datetimeoffset, r.Date) AT TIME ZONE '$tz' as date) as Date
                    FROM [$db].[dbo].[DialingResults] r WITH(NOLOCK)
                    WHERE r.GroupId = :group_id3$i
                    AND r.CallStatus = 'AGENTSPCB'
                    AND r.Date >= :startdate3$i
                    AND r.Date < :enddate3$i
                GROUP BY r.Rep, r.Campaign, r.GroupId, CAST(CONVERT(datetimeoffset, r.Date) AT TIME ZONE '$tz' as date)) a
            WHERE #AgentAnalysis.Campaign = a.Campaign
            AND #AgentAnalysis.Rep = a.Rep
            AND #AgentAnalysis.Date = a.Date;
            ";
        }

        $sql .= "UPDATE #AgentAnalysis
        SET Connects = a.Connects
        FROM (SELECT Date, Rep, Campaign, SUM([Count]) as Connects
                FROM #DialingResultsStats
                WHERE [Type] > 0
                GROUP BY Campaign, Rep, Date) a
        WHERE #AgentAnalysis.Campaign = a.Campaign
        AND #AgentAnalysis.Rep = a.Rep
        AND	#AgentAnalysis.Date = a.Date;

        UPDATE #AgentAnalysis
        SET Contacts = a.Contacts
        FROM (SELECT Date, Rep, Campaign, SUM([Count]) as Contacts
                FROM #DialingResultsStats
                WHERE	[Type] > 1
                GROUP BY Campaign, Rep, Date) a
        WHERE #AgentAnalysis.Campaign = a.Campaign
        AND #AgentAnalysis.Rep = a.Rep
        AND	#AgentAnalysis.Date = a.Date;

        UPDATE #AgentAnalysis
        SET Leads = a.Leads
        FROM (SELECT Date, Rep, Campaign, SUM([Count]) as Leads
                FROM #DialingResultsStats
                WHERE [Type] = 3
                GROUP BY Campaign, Rep, Date) a
        WHERE #AgentAnalysis.Campaign = a.Campaign
        AND #AgentAnalysis.Rep = a.Rep
        AND #AgentAnalysis.Date = a.Date;

        UPDATE #AgentAnalysis
        SET	AvWaitTime = a.AvWaitTime
        FROM (SELECT Date, Rep, Campaign, SUM(Duration)/SUM([Count]) as AvWaitTime
            FROM #AgentActivityDuration
            WHERE [Action] = 'Waiting'
            GROUP BY Date, Rep, Campaign) a
        WHERE #AgentAnalysis.Campaign = a.Campaign
        AND #AgentAnalysis.Rep = a.Rep
        AND #AgentAnalysis.Date = a.Date;

        UPDATE #AgentAnalysis
        SET	AvTalkTime = a.AvTalkTime
        FROM (SELECT Date, Rep, Campaign, SUM(Duration)/SUM([Count]) as AvTalkTime
                FROM #AgentActivityDuration
                WHERE [Action] in ('Call', 'ManualCall', 'InboundCall')
                GROUP BY Campaign, Rep, Date) a
        WHERE #AgentAnalysis.Campaign = a.Campaign
        AND #AgentAnalysis.Rep = a.Rep
        AND #AgentAnalysis.Date = a.Date;

        UPDATE #AgentAnalysis
        SET LoggedInTimeSec = a.Hours
        FROM (SELECT Rep, Campaign, SUM(Duration) as Hours, Date
            FROM #AgentActivityDuration
            GROUP BY Campaign, Rep, Date) a
        WHERE #AgentAnalysis.Campaign = a.Campaign
        AND #AgentAnalysis.Rep = a.Rep
        AND	#AgentAnalysis.Date = a.Date;

        UPDATE #AgentAnalysis
        SET AvailTimeSec = a.AvailTime
        FROM (SELECT Rep, Campaign, SUM(Duration) as AvailTime, Date
                FROM #AgentActivityDuration
            WHERE [Action] = 'Waiting'
            GROUP BY Date, Campaign, Rep) a
        WHERE #AgentAnalysis.Campaign = a.Campaign
        AND #AgentAnalysis.Rep = a.Rep
        AND #AgentAnalysis.Date = a.Date;

        UPDATE #AgentAnalysis
        SET PausedTimeSec = a.PausedTime
        FROM (SELECT Rep, Campaign, SUM(Duration) as PausedTime, Date
                FROM #AgentActivityDuration
                WHERE [Action] = 'Paused'
                GROUP BY Campaign, Rep, Date) a
        WHERE #AgentAnalysis.Campaign = a.Campaign
        AND #AgentAnalysis.Rep = a.Rep
        AND #AgentAnalysis.Date = a.Date;

        UPDATE #AgentAnalysis
        SET Hours = IsNull(a.LoggedInTime/3600, 0)
        FROM (SELECT Rep, Campaign, SUM(Duration) as LoggedInTime, Date
                FROM  #AgentActivityDuration
            WHERE [Action] <> 'Paused'
            GROUP BY Campaign, Rep, Date) a
        WHERE #AgentAnalysis.Campaign = a.Campaign
        AND #AgentAnalysis.Rep = a.Rep
        AND	#AgentAnalysis.Date = a.Date;

        UPDATE #AgentAnalysis
        SET ConnectedTimeSec = a.ConnectedTime
        FROM (SELECT Rep, Campaign, SUM(Duration) as ConnectedTime, Date
                FROM #AgentActivityDuration
            WHERE [Action] in ('Call', 'ManualCall', 'InboundCall')
            GROUP BY Campaign, Rep, Date) a
        WHERE #AgentAnalysis.Campaign = a.Campaign
        AND	#AgentAnalysis.Rep = a.Rep
        AND	#AgentAnalysis.Date = a.Date;

        UPDATE #AgentAnalysis
        SET DispositionTimeSec = a.DispositionTime
        FROM (SELECT Rep, Campaign, SUM(Duration) as DispositionTime, Date
                FROM #AgentActivityDuration
                WHERE [Action] = 'Disposition'
                GROUP BY Campaign, Rep, Date) a
        WHERE #AgentAnalysis.Campaign = a.Campaign
        AND	#AgentAnalysis.Rep = a.Rep
        AND	#AgentAnalysis.Date = a.Date;

        UPDATE #AgentAnalysis
        SET CPH = Connects/Hours,
            APH = Leads/Hours
        WHERE Hours > 0;

        UPDATE #AgentAnalysis
        SET ConversionRate = (CAST(Leads as numeric(18,2)) / CAST(Contacts as numeric(18,2))) * 100
        WHERE Contacts > 0;

        UPDATE #AgentAnalysis
        SET ConversionFactor = (CAST(Leads as numeric(18,2)) /CAST(Contacts as numeric(18,2))) / Hours
        WHERE Hours > 0
        AND Contacts > 0;

        SELECT *, totRows = COUNT(*) OVER()
        FROM #AgentAnalysis
        WHERE Hours > 0";

        // Check params
        if (!empty($this->params['orderby']) && is_array($this->params['orderby'])) {
            $sort = '';
            foreach ($this->params['orderby'] as $col => $dir) {
                $sort .= ",$col $dir";
            }
            $sql .= ' ORDER BY ' . substr($sort, 1);
        } else {
            $sql .= ' ORDER BY Date, Rep, Campaign';
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
            $this->params['totrows'] = $results[0]['totRows'];

            foreach ($results as &$rec) {
                array_pop($rec);
                $rec['Date'] = date('m/d/Y', strtotime($rec['Date']));
                $rec['AvTalkTime'] = SecondsToHms($rec['AvTalkTime']);
                $rec['AvWaitTime'] = SecondsToHms($rec['AvWaitTime']);
                $rec['AvailTimeSec'] = SecondsToHms($rec['AvailTimeSec']);
                $rec['PausedTimeSec'] = SecondsToHms($rec['PausedTimeSec']);
                $rec['ConnectedTimeSec'] = SecondsToHms($rec['ConnectedTimeSec']);
                $rec['DispositionTimeSec'] = SecondsToHms($rec['DispositionTimeSec']);
                $rec['LoggedInTimeSec'] = SecondsToHms($rec['LoggedInTimeSec']);
            }
            $this->params['totpages'] = floor($this->params['totrows'] / $this->params['pagesize']);
            $this->params['totpages'] += floor($this->params['totrows'] / $this->params['pagesize']) == ($this->params['totrows'] / $this->params['pagesize']) ? 0 : 1;
        }

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

        if (!empty($request->skills)) {
            $this->params['skills'] = $request->skills;
        }

        // Save params to session
        $this->saveSessionParams();

        return $this->errors;
    }
}
