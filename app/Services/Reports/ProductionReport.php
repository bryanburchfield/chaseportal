<?php

namespace App\Services\Reports;

use App\Traits\CampaignTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\ReportTraits;

class ProductionReport
{
    use ReportTraits;
    use CampaignTraits;

    public function __construct()
    {
        $this->initilaizeParams();

        $this->params['reportName'] = trans('reports.production_report');
        $this->params['fromdate'] = date("m/d/Y 9:00 \A\M");
        $this->params['todate'] = date("m/d/Y 8:00 \P\M");
        $this->params['campaigns'] = [];
        $this->params['skills'] = [];
        $this->params['hasTotals'] = true;
        $this->params['columns'] = [];
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

    private function executeReport($all = false)
    {
        list($fromDate, $toDate) = $this->dateRange($this->params['fromdate'], $this->params['todate']);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');
        $campaigns = str_replace("'", "''", implode('!#!', $this->params['campaigns']));

        // Check params
        if (!empty($this->params['orderby']) && is_array($this->params['orderby'])) {
            $sort = '';
            foreach ($this->params['orderby'] as $col => $dir) {
                $sort .= ",[$col] $dir";
            }
            $orderby = ' ORDER BY ' . substr($sort, 1);
        } else {
            $orderby = ' ORDER BY [Rep]';
        }

        $bind['group_id20'] =  Auth::user()->group_id;
        $bind['startdate20'] = $startDate;
        $bind['enddate20'] = $endDate;
        $bind['orderby'] = $orderby;

        $sql = "SET NOCOUNT ON;";

        if (!empty($this->params['skills'])) {
            $skills = str_replace("'", "''", implode('!#!', $this->params['skills']));
            $bind['skills'] = $skills;

            $sql .= "
            CREATE TABLE #SelectedSkill(SkillName varchar(50) Primary Key);
            INSERT INTO #SelectedSkill SELECT DISTINCT [value] from dbo.SPLIT(:skills, '!#!');";
        }

        $sql .= "
        CREATE TABLE #SelectedCampaign(CampaignName varchar(50) Primary Key)";

        if (!empty($campaigns)) {
            $bind['campaigns'] = $campaigns;

            $sql .= "
            INSERT INTO #SelectedCampaign SELECT DISTINCT [value] from dbo.SPLIT(:campaigns, '!#!');";
        }

        $sql .= "
        DECLARE
            @cols NVARCHAR(MAX),
            @params NVARCHAR(500),
            @query NVARCHAR(MAX),
            @temp_table_name NVARCHAR(100)

        SET @cols = ''
        SET @temp_table_name = '[##' + REPLACE(NEWID(), '-','') + ']'

        SELECT  @cols = STUFF((
          SELECT '],[' + t2.CallStatus
          FROM (";

        $union = "";
        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id' . $i] = Auth::user()->group_id;
            $bind['startdate' . $i] = $startDate;
            $bind['enddate' . $i] = $endDate;

            $sql .= "
                    $union SELECT dr.CallStatus
                    FROM [$db].[dbo].[DialingResults] dr WITH(NOLOCK)
                    LEFT JOIN [$db].[dbo].[Dispos] d on d.Disposition = dr.CallStatus";

            if (!empty($campaigns)) {
                $sql .= "
                    INNER JOIN #SelectedCampaign c on c.CampaignName = dr.Campaign";
            }

            if (!empty($this->params['skills'])) {
                $sql .= "
                    INNER JOIN [$db].[dbo].[Reps] RR on RR.RepName COLLATE SQL_Latin1_General_CP1_CS_AS = dr.Rep
                    INNER JOIN #SelectedSkill SS on SS.SkillName COLLATE SQL_Latin1_General_CP1_CS_AS = RR.Skill";
            }

            $sql .= "
                    WHERE dr.GroupId = :group_id$i
                    AND dr.Date >= :startdate$i
                    AND dr.Date < :enddate$i
                    AND (((d.GroupId=dr.GroupId OR d.IsSystem=1) AND (d.Campaign=dr.Campaign OR d.IsDefault=1) AND d.Type > 0) OR dr.CallStatus = 'UNFINISHED')
                    GROUP BY dr.CallStatus, dr.GroupId";
            $union = "UNION";
        }
        $sql .= ") AS t2
            ORDER BY '],[' + t2.CallStatus
            FOR XML PATH('')
            ), 1, 2, '') + ']'

        SET @query = N'SELECT Rep, CAST(0 as numeric(18,2)) as ManHours, '+
        @cols +', CAST(0 as int) as Connects, CAST(0 as int) as Contacts, CAST(0 as numeric(18,2)) as ContactsPerHour, CAST(0 as int) as SalesCount, CAST(0 as numeric(18,2)) as SalesPerHour INTO ' + @temp_table_name + N'
        FROM (";

        $union = "";
        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id1' . $i] = Auth::user()->group_id;
            $bind['startdate1' . $i] = $startDate;
            $bind['enddate1' . $i] = $endDate;

            $sql .= "
            $union SELECT dr.CallStatus, dr.Rep
            FROM [$db].[dbo].[DialingResults] dr WITH(NOLOCK)
            LEFT JOIN Dispos d on d.Disposition = dr.CallStatus";

            if (!empty($this->params['skills'])) {
                $sql .= "
                INNER JOIN [$db].[dbo].[Reps] RR on RR.RepName COLLATE SQL_Latin1_General_CP1_CS_AS = dr.Rep
                INNER JOIN #SelectedSkill SS on SS.SkillName COLLATE SQL_Latin1_General_CP1_CS_AS = RR.Skill";
            }

            if (!empty($campaigns)) {
                $sql .= "
                INNER JOIN #SelectedCampaign c on c.CampaignName = dr.Campaign";
            }

            $sql .= "
                WHERE dr.GroupId = '''+CAST(:group_id1$i as varchar)+''' AND '

        SET @query = @query + N'dr.Date between '''+CAST(:startdate1$i as nvarchar)+''' and '''+CAST(:enddate1$i as nvarchar)+''' AND
                (((d.GroupId=dr.GroupId OR d.IsSystem=1)  AND (d.Campaign=dr.Campaign OR d.IsDefault=1) AND d.Type > 0) OR
                dr.CallStatus = ''UNFINISHED'')";

            $union = 'UNION';
        }

        $sql .= ") p
        PIVOT
        (
        count(CallStatus)
        FOR CallStatus IN
        ( '+
        @cols +' )
        ) AS pvt
        ORDER BY Rep'

        SET @params = '@CampaignIN varchar(max)'

        BEGIN TRY";

        if (!empty($campaigns)) {
            $sql .= "
            execute sp_executesql @query";
        } else {
            $bind['campaigns1'] = $campaigns;

            $sql .= "
            execute sp_executesql
                @query, @params,
                @CampaignIN=:campaigns1";
        }

        $sql .= "
            IF OBJECT_ID('tempdb..' + @temp_table_name ) IS NULL
                RETURN

            set @query = N'UPDATE ' + @temp_table_name + N' SET ManHours = IsNull(a.ManHours/3600, 0)
            FROM (SELECT Rep, SUM(Duration) as ManHours
                  FROM AgentActivity aa WITH(NOLOCK)'";

        if (!empty($campaigns)) {
            $sql .= "
                set @query = @query + N' INNER JOIN #SelectedCampaign c on c.CampaignName = aa.Campaign'";
        }

        $sql .= "
            set @query = @query + N' WHERE aa.GroupId = ' + CAST(:group_id20 as nvarchar) + N' AND '

            set @query = @query + N'aa.Date >= '''+CAST(:startdate20 as nvarchar)+''' AND aa.Date < '''+CAST(:enddate20 as nvarchar)+''' AND
                    [Action] <> ''Paused''
                GROUP BY Rep) a
            WHERE
                ' + @temp_table_name + N'.Rep = a.Rep'";

        if (!empty($campaigns)) {
            $bind['campaigns20'] = $campaigns;

            $sql .= "
            execute sp_executesql
            @query, @params,
            @CampaignIN=:campaigns20";
        } else {
            $sql .= "
            execute sp_executesql @query";
        }

        $sql .= "
            CREATE TABLE #DialingResultsStats(
                Rep varchar(50) COLLATE SQL_Latin1_General_CP1_CS_AS,
                [Type] int,
                [Count] int
            )

                INSERT INTO #DialingResultsStats
                SELECT Rep, Type, SUM(Cnt) as [Count]
                FROM (";

        $union = '';
        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id3' . $i] = Auth::user()->group_id;
            $bind['startdate3' . $i] = $startDate;
            $bind['enddate3' . $i] = $endDate;

            $sql .= "
                $union SELECT r.Rep, d.Type, count(r.id) as [Cnt]
                FROM [$db].[dbo].[DialingResults] r WITH(NOLOCK)
                    CROSS APPLY (SELECT TOP 1 [Type] FROM Dispos WHERE Disposition=r.CallStatus AND
                            (GroupId=r.GroupId OR IsSystem=1) AND (Campaign=r.Campaign OR Campaign='')
                            ORDER BY [Description] Desc) d";

            if (!empty($campaigns)) {
                $sql .= "
                    INNER JOIN #SelectedCampaign c on c.CampaignName = r.Campaign";
            }

            if (!empty($this->params['skills'])) {
                $sql .= "
                    INNER JOIN [$db].[dbo].[Reps] RR on RR.RepName COLLATE SQL_Latin1_General_CP1_CS_AS = r.Rep
                    INNER JOIN #SelectedSkill SS on SS.SkillName COLLATE SQL_Latin1_General_CP1_CS_AS = RR.Skill";
            }

            $sql .= "
                WHERE r.GroupId = :group_id3$i
                AND r.Date >= :startdate3$i
                AND r.Date < :enddate3$i
                AND d.Type > 0
                GROUP BY r.Rep, d.Type";

            $union = 'UNION ALL';
        }
        $sql .= ") tmp
                GROUP BY Rep, Type

            CREATE INDEX IX_CampaignType ON #DialingResultsStats (Rep, [Type]);
            CREATE INDEX IX_Type ON #DialingResultsStats ([Type]);

            set @query = N'UPDATE ' + @temp_table_name + N' SET
                Connects = a.Connects
            FROM (SELECT Rep, SUM([Count]) as Connects
                FROM #DialingResultsStats
                WHERE [Type] > 0
                GROUP BY Rep) a
            WHERE ' + @temp_table_name + N'.Rep = a.Rep'

            execute sp_executesql @query

            set @query = N'UPDATE ' + @temp_table_name + N' SET
                Contacts = a.Contacts
            FROM (SELECT Rep, SUM([Count]) as Contacts
                FROM #DialingResultsStats
                WHERE [Type] > 1
                GROUP BY Rep) a
            WHERE ' + @temp_table_name + N'.Rep = a.Rep'

            execute sp_executesql @query

            set @query = N'UPDATE ' + @temp_table_name + N' SET
                SalesCount = a.SalesCount
            FROM (SELECT Rep, SUM([Count]) as SalesCount
                FROM #DialingResultsStats
                WHERE [Type] = 3
                GROUP BY Rep) a
            WHERE ' + @temp_table_name + N'.Rep = a.Rep'

            execute sp_executesql @query

            set @query = N'UPDATE ' + @temp_table_name + N' SET
                ContactsPerHour = Contacts/ManHours,
                SalesPerHour = SalesCount/ManHours
            WHERE ManHours > 0'

            execute sp_executesql @query

            DROP TABLE #DialingResultsStats
        END TRY

        BEGIN CATCH

            DECLARE @ErrorMessage NVARCHAR(4000);
            DECLARE @ErrorSeverity INT;
            DECLARE @ErrorState INT;

            SELECT @ErrorMessage = ERROR_MESSAGE(),
                    @ErrorSeverity = ERROR_SEVERITY(),
                    @ErrorState = ERROR_STATE();

            RAISERROR (@ErrorMessage,
                        @ErrorSeverity,
                        @ErrorState
                        );
            return
        END CATCH

        SET @query = 'SELECT * FROM ' + @temp_table_name  + ' ' + :orderby
        execute sp_executesql @query";

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

        return $this->getPage($results);
    }

    private function processResults($results)
    {
        if (!count($results)) return $results;

        // Columns are variable, so set them now
        $this->params['columns'] = [];
        foreach ($results[0] as $k => $v) {
            $this->params['columns'][] = $k;
        }

        // this sets the order of the columns
        foreach ($this->params['columns'] as $k => $v) {
            $total[$v] = '';
        }

        foreach ($this->params['columns'] as $k) {
            $total[$k] = 0;
        }
        $total['Rep'] = 'Total:';

        foreach ($results as &$rec) {
            foreach ($rec as $k => $v) {
                if ($k != 'Rep') {
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
