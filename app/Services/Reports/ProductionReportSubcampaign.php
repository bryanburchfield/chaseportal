<?php

namespace App\Services\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\ReportTraits;

class ProductionReportSubcampaign
{
    use ReportTraits;

    public function __construct()
    {
        $this->initilaizeParams();

        $this->params['reportName'] = 'Production Report by Subcampaign';
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
                new \DateTime($this->params['fromdate']),
                new \DateTime($this->params['todate'])
            ),
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
        $campaigns = str_replace("'", "''", implode('!#!', $this->params['campaigns']));

        // Check params
        if (!empty($this->params['orderby']) && is_array($this->params['orderby'])) {
            $sort = '';
            foreach ($this->params['orderby'] as $col => $dir) {
                $sort .= ",[$col] $dir";
            }
            $orderby = ' ORDER BY ' . substr($sort, 1);
        } else {
            $orderby = ' ORDER BY [Subcampaign]';
        }

        $bind['group_id1'] =  Auth::user()->group_id;
        $bind['group_id2'] =  Auth::user()->group_id;
        $bind['group_id3'] =  Auth::user()->group_id;
        $bind['group_id4'] =  Auth::user()->group_id;
        $bind['group_id5'] =  Auth::user()->group_id;
        $bind['group_id6'] =  Auth::user()->group_id;
        $bind['startdate1'] = $startDate;
        $bind['enddate1'] = $endDate;
        $bind['startdate2'] = $startDate;
        $bind['enddate2'] = $endDate;
        $bind['startdate3'] = $startDate;
        $bind['enddate3'] = $endDate;
        $bind['orderby'] = $orderby;

        $sql = "SET NOCOUNT ON;";

        if (!empty($campaigns)) {
            $sql .= "
            CREATE TABLE #SelectedCampaign(CampaignName varchar(50) Primary Key)
            INSERT INTO #SelectedCampaign SELECT DISTINCT [value] from dbo.SPLIT(:campaigns, '!#!')";
        }

        if (!empty($this->params['skills'])) {
            $list = str_replace("'", "''", implode('!#!', $this->params['skills']));
            $sql .= "
            CREATE TABLE #SelectedSkill(SkillName varchar(50) Primary Key);
            INSERT INTO #SelectedSkill SELECT DISTINCT [value] from dbo.SPLIT('$list', '!#!');";
        }

        $sql .= "
        DECLARE
            @cols NVARCHAR(MAX),
            @query NVARCHAR(MAX),
            @temp_table_name NVARCHAR(100)

        SET @cols = ''
        SET @temp_table_name = '[##' + REPLACE(NEWID(), '-','') + ']';

        SELECT  @cols = STUFF(( SELECT '],[' + t2.CallStatus
        FROM (";

        $union = '';
        foreach (Auth::user()->getDatabaseArray() as $db) {
            $sql .= " $union SELECT DISTINCT dr.CallStatus
                FROM [$db].[dbo].[DialingResults] dr WITH(NOLOCK)";

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
                LEFT JOIN [$db].[dbo].[Dispos] d on d.Disposition = dr.CallStatus
                WHERE dr.GroupId = :group_id1
                AND dr.Date >= :startdate1
                AND dr.Date < :enddate1
                AND (((d.GroupId=:group_id2 OR d.IsSystem=1) AND (d.Campaign=dr.Campaign OR d.IsDefault=1) AND d.Type > 0) OR dr.CallStatus = 'UNFINISHED')";

            $union = 'UNION';
        }
        $sql .= "
            ) AS t2
        ORDER BY '],[' + t2.CallStatus
        FOR XML PATH('')), 1, 2, '') + ']'

        SET @query = N'SELECT Subcampaign, '+
        @cols +', CAST(0 as int) as Connects,
        CAST(0 as int) as Contacts,
        CAST(0 as int) as SalesCount
        INTO ' + @temp_table_name + N'
        FROM (";

        $union = '';
        foreach (Auth::user()->getDatabaseArray() as $db) {
            $sql .= " $union SELECT dr.CallStatus, dr.Subcampaign
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
            WHERE dr.GroupId = '''+CAST(:group_id3 as varchar)+'''
            AND dr.Date >= '''+CAST(:startdate2 as nvarchar)+'''
            AND dr.Date < '''+CAST(:enddate2 as nvarchar)+'''
            AND (((d.GroupId='''+CAST(:group_id4 as varchar)+''' OR d.IsSystem=1)
                AND (d.Campaign=dr.Campaign OR d.IsDefault=1) AND d.Type > 0)
                OR dr.CallStatus = ''UNFINISHED'')";

            $union = 'UNION ALL';
        }

        $sql .= "
        ) p
        PIVOT
        (
         count(CallStatus) FOR CallStatus IN ( '+ @cols +' )
        ) AS pvt
        ORDER BY Subcampaign'

        execute sp_executesql @query

        IF OBJECT_ID('tempdb..' + @temp_table_name ) IS NULL
            RETURN

        CREATE TABLE #DialingResultsStats(
            Subcampaign varchar(50),
            [Type] int,
            [Count] int
        )

        INSERT INTO #DialingResultsStats
        SELECT Subcampaign, Type, SUM([Count]) as [Count] FROM (";

        $union = '';
        foreach (Auth::user()->getDatabaseArray() as $db) {
            $sql .= " $union SELECT r.Subcampaign, d.Type, count(r.id) as [Count]
            FROM [$db].[dbo].[DialingResults] r WITH(NOLOCK)
                CROSS APPLY (SELECT TOP 1 [Type]
                            FROM [$db].[dbo].[Dispos]
                            WHERE Disposition=r.CallStatus
                            AND (GroupId=:group_id5 OR IsSystem=1) AND (Campaign=r.Campaign OR Campaign='')
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
            WHERE r.GroupId = :group_id6
            AND r.Date >= :startdate3
            AND r.Date < :enddate3
            AND d.Type > 0
            GROUP BY r.Subcampaign, d.Type";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp
        GROUP BY Subcampaign, Type

        CREATE INDEX IX_CampaignType ON #DialingResultsStats (Subcampaign, [Type]);
        CREATE INDEX IX_Type ON #DialingResultsStats ([Type]);

        set @query = N'UPDATE ' + @temp_table_name + N' SET
            Connects = a.Connects
        FROM (SELECT Subcampaign, SUM([Count]) as Connects
            FROM #DialingResultsStats
            WHERE [Type] > 0
            GROUP BY Subcampaign) a
        WHERE ' + @temp_table_name + N'.Subcampaign = a.Subcampaign'

        execute sp_executesql @query

        set @query = N'UPDATE ' + @temp_table_name + N' SET
            Contacts = a.Contacts
        FROM (SELECT Subcampaign, SUM([Count]) as Contacts
            FROM #DialingResultsStats
            WHERE [Type] > 1
            GROUP BY Subcampaign) a
        WHERE ' + @temp_table_name + N'.Subcampaign = a.Subcampaign'

        execute sp_executesql @query

        set @query = N'UPDATE ' + @temp_table_name + N' SET
            SalesCount = a.SalesCount
        FROM (SELECT Subcampaign, SUM([Count]) as SalesCount
            FROM #DialingResultsStats
            WHERE [Type] = 3
            GROUP BY Subcampaign) a
        WHERE ' + @temp_table_name + N'.Subcampaign = a.Subcampaign'

        execute sp_executesql @query

        SET @query = 'SELECT * FROM ' + @temp_table_name  + ' ' + :orderby
        execute sp_executesql @query";

        // Log::debug($sql);
        // Log::debug($bind);

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
        if (!count($results)) return $results;

        // Columns are variable, so set them now
        $this->params['columns'] = [];
        foreach ($results[0] as $k => $v) {
            $this->params['columns'][] = $k;
        }

        $total = [];

        // this sets the order of the columns
        foreach ($this->params['columns'] as $k => $v) {
            $total[$v] = '';
        }

        foreach ($this->params['columns'] as $k) {
            $total[$k] = 0;
        }
        $total['Subcampaign'] = 'Total:';

        foreach ($results as &$rec) {
            foreach ($rec as $k => $v) {
                if ($k != 'Subcampaign') {
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
