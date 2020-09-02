<?php

namespace App\Services\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\ReportTraits;
use Illuminate\Support\Carbon;

class ApnSubcampaignSummary
{
    use ReportTraits;

    public function __construct()
    {
        $this->initilaizeParams();

        $this->params['reportName'] = 'reports.subcampaign_summary';
        $this->params['columns'] = [
            'Date' => 'reports.date',
            'Campaign' => 'reports.campaign',
            'Subcampaign' => 'reports.subcampaign',
            'Total' => 'reports.total_leads',
            'Dialed' => 'reports.dialed',
            'DPH' => 'reports.dph',
            'Available' => 'reports.available',
            'AvAttempt' => 'reports.avattempt',
            'ManHours' => 'reports.manhours',
            'Connects' => 'reports.connects',
            'CPH' => 'reports.cph',
            'Sales' => 'reports.sales',
            'APH' => 'reports.aph',
            'ConnectRate' => 'reports.connectrate',
            'SaleRateValue' => 'reports.saleratevalue',
            'ConversionRate' => 'reports.conversionrate',
            'ConversionFactor' => 'reports.conversionfactor',
            'Cepts' => 'reports.cepts',
            'ThresholdCalls' => 'reports.threshold_calls',
            'ThresholdRatio' => 'reports.threshold_ratio',
            'ThresholdClosingPct' => 'reports.threshold_closing_pct',
        ];
    }

    public function getFilters()
    {
        $filters = [
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
            $this->params['totrows'] = $results[0]['totRows'];

            foreach ($results as &$rec) {
                $rec = $this->processRow($rec);
            }
            $this->params['totpages'] = floor($this->params['totrows'] / $this->params['pagesize']);
            $this->params['totpages'] += floor($this->params['totrows'] / $this->params['pagesize']) == ($this->params['totrows'] / $this->params['pagesize']) ? 0 : 1;
        }

        return $results;
    }

    public function makeQuery($all)
    {
        $this->setHeadings();

        list($fromDate, $toDate) = $this->dateRange($this->params['fromdate'], $this->params['todate']);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $tz =  Auth::user()->tz;

        $bind['group_id'] = Auth::user()->group_id;
        $bind['threshold_secs1'] = $this->params['threshold_secs'];
        $bind['threshold_secs2'] = $this->params['threshold_secs'];

        $sql = "SET NOCOUNT ON;

        CREATE TABLE #SubcampaignSummary(
            Date varchar(50),
            Campaign varchar(50),
            Subcampaign varchar(50),
            Total int DEFAULT 0,
            Dialed int DEFAULT 0,
            DPH numeric(18,2) DEFAULT 0,
            Available numeric(18,2) DEFAULT 0,
            AvAttempt int DEFAULT 0,
            ManHours numeric(18,2) DEFAULT 0,
            Connects int DEFAULT 0,
            CPH numeric(18,2) DEFAULT 0,
            Sales int DEFAULT 0,
            APH numeric(18,2) DEFAULT 0,
            ConnectRate numeric(18,2) DEFAULT 0,
            SaleRateValue numeric(18,2) DEFAULT 0,
            ConversionRate numeric(18,2) DEFAULT 0,
            ConversionFactor numeric(18,2) DEFAULT 0,
            Cepts int DEFAULT 0,
            CeptsPercentage numeric(18,2) DEFAULT 0,
            TalkTimeCount int DEFAULT 0,
            ThresholdCalls int DEFAULT 0,
            ThresholdSales int DEFAULT 0,
            ThresholdRatio numeric(18,2) DEFAULT 0,
            ThresholdClosingPct numeric(18,2) DEFAULT 0
        );

        CREATE UNIQUE INDEX IX_CampaignDate ON #SubcampaignSummary (Campaign, Subcampaign, Date);

        SELECT * INTO #DialingResultsStats FROM (";

        $union = '';
        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id' . $i] = Auth::user()->group_id;
            $bind['startdate' . $i] = $startDate;
            $bind['enddate' . $i] = $endDate;

            $sql .= " $union SELECT
            CAST(CONVERT(datetimeoffset, Date) AT TIME ZONE '$tz' as date) as Date,
            dr.Campaign,
            IsNull(dr.Subcampaign, '') as Subcampaign,
            dr.CallStatus as CallStatus,
            IsNull((SELECT TOP 1 [Type]
              FROM [$db].[dbo].[Dispos]
              WHERE Disposition=dr.CallStatus AND (GroupId=dr.GroupId OR IsSystem=1) AND (Campaign=dr.Campaign OR Campaign='') ORDER BY [id]), 0) as [Type],
            count(dr.CallStatus) as [Count]
            FROM [$db].[dbo].[DialingResults] dr WITH(NOLOCK)
            WHERE dr.GroupId = :group_id$i
            AND dr.Date >= :startdate$i
            AND dr.Date < :enddate$i
            AND dr.Campaign <> '_MANUAL_CALL_'
            AND IsNull(dr.CallStatus, '') <> ''
            AND dr.CallStatus not in ('CR_CNCT/CON_CAD', 'CR_CNCT/CON_PVD')";

            if (session('ssoRelativeCampaigns', 0)) {
                $sql .= " AND dr.Campaign IN (SELECT CampaignName FROM dbo.GetAllRelativeCampaigns(:ssousercamp1$i, 1))";
                $bind['ssousercamp1' . $i] = session('ssoUsername');
            }

            if (session('ssoRelativeReps', 0)) {
                $sql .= " AND dr.Rep IN (SELECT RepName FROM dbo.GetAllRelativeReps(:ssouserrep1$i))";
                $bind['ssouserrep1' . $i] = session('ssoUsername');
            }

            $sql .= "
            GROUP BY dr.Campaign, IsNull(dr.Subcampaign, ''), dr.CallStatus, dr.GroupId, CAST(CONVERT(datetimeoffset, Date) AT TIME ZONE '$tz' as date)";

            $union = 'UNION ALL';
        }

        $sql .=
            ") tmp;

        CREATE INDEX IX_CampaignType ON #DialingResultsStats (Campaign, Subcampaign, [Type], Date);
        CREATE INDEX IX_Type ON #DialingResultsStats ([Type], Date);

        SELECT * INTO #Sales FROM (";

        $union = '';
        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id_sales' . $i] =  Auth::user()->group_id;
            $bind['startdate_sales' . $i] = $startDate;
            $bind['enddate_sales' . $i] = $endDate;

            $sql .= " $union SELECT DR.Rep, DR.ActivityId
            FROM [$db].[dbo].[DialingResults] as DR WITH(NOLOCK)
            CROSS APPLY (SELECT TOP 1 [Type]
                FROM  [Dispos] DI
                WHERE Disposition = DR.CallStatus
                AND (GroupId = DR.GroupId OR IsSystem=1)
                AND (Campaign = DR.Campaign OR Campaign = '')
                ORDER BY [id]) DI";

            $sql .= "
            WHERE DR.GroupId = :group_id_sales$i
            AND DR.Date >= :startdate_sales$i
            AND DR.Date < :enddate_sales$i
            AND DI.Type = 3";

            if (session('ssoRelativeCampaigns', 0)) {
                $sql .= " AND DR.Campaign IN (SELECT CampaignName FROM dbo.GetAllRelativeCampaigns(:ssousercamp_sales$i, 1))";
                $bind['ssousercamp_sales' . $i] = session('ssoUsername');
            }

            $union = 'UNION ALL';
        }

        $sql .=
            ") tmp;

        SELECT * INTO #AgentSummaryDuration FROM (";

        $union = '';
        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id_dur' . $i] =  Auth::user()->group_id;
            $bind['startdate_dur' . $i] = $startDate;
            $bind['enddate_dur' . $i] = $endDate;

            $sql .= " $union SELECT aa.Rep, [Action], aa.Duration, aa.ActivityId, aa.Campaign, aa.Subcampaign
            FROM [$db].[dbo].[AgentActivity] as aa WITH(NOLOCK)
            WHERE aa.GroupId = :group_id_dur$i
            AND aa.Date >= :startdate_dur$i
            AND aa.Date < :enddate_dur$i
            AND aa.Duration > 0";

            if (session('ssoRelativeCampaigns', 0)) {
                $sql .= " AND aa.Campaign IN (SELECT CampaignName FROM dbo.GetAllRelativeCampaigns(:ssousercamp_dur$i, 1))";
                $bind['ssousercamp_dur' . $i] = session('ssoUsername');
            }

            $union = 'UNION ALL';
        }

        $sql .= ") tmp;

        CREATE INDEX IX_ASCampaignType ON #AgentSummaryDuration (Campaign, Subcampaign);

        INSERT #SubcampaignSummary(Campaign, Subcampaign, Date)
        SELECT Campaign, Subcampaign, Date
        FROM #DialingResultsStats
        GROUP BY Campaign, Subcampaign, Date

        CREATE TABLE #DialingSettings
        (
            Campaign varchar(50),
            MaxDialingAttempts int
        )

        INSERT INTO #DialingSettings(Campaign, MaxDIalingAttempts)
        SELECT Campaign, dbo.GetGroupCampaignSetting(:group_id, Campaign, 'MaxDialingAttempts', 0)
        FROM #SubcampaignSummary
        GROUP BY Campaign

        UPDATE #SubcampaignSummary
        SET Connects = a.Connects
        FROM (SELECT Campaign, Subcampaign, SUM([Count]) as Connects, Date
              FROM #DialingResultsStats
              WHERE [Type] > 0
              GROUP BY Campaign, Subcampaign, Date) a
        WHERE #SubcampaignSummary.Campaign = a.Campaign
        AND #SubcampaignSummary.Subcampaign = a.Subcampaign
        AND #SubcampaignSummary.Date = a.Date

        UPDATE #SubcampaignSummary
        SET Sales = a.Sales
        FROM (SELECT Campaign, Subcampaign, SUM([Count]) as Sales, Date
              FROM #DialingResultsStats
              WHERE [Type] = 3
              GROUP BY Campaign, Subcampaign, Date) a
        WHERE #SubcampaignSummary.Campaign = a.Campaign
        AND #SubcampaignSummary.Subcampaign = a.Subcampaign
        AND #SubcampaignSummary.Date = a.Date

        UPDATE #SubcampaignSummary
        SET Cepts = a.Cepts
        FROM (SELECT Campaign, Subcampaign, SUM([Count]) as Cepts
              FROM #DialingResultsStats
              WHERE CallStatus = 'CR_CEPT'
              GROUP BY Campaign, Subcampaign) a
        WHERE #SubcampaignSummary.Campaign = a.Campaign
        AND #SubcampaignSummary.Subcampaign = a.Subcampaign

        UPDATE #SubcampaignSummary
        SET ManHours = a.ManHours/3600 FROM (
           SELECT Campaign, Subcampaign, Date, SUM(IsNull(ManHours, 0)) as ManHours FROM (";

        $union = '';
        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id1' . $i] = Auth::user()->group_id;
            $bind['startdate1' . $i] = $startDate;
            $bind['enddate1' . $i] = $endDate;

            $sql .= " $union SELECT
                Campaign,
                IsNull(Subcampaign, '') as Subcampaign,
                CAST(CONVERT(datetimeoffset, Date) AT TIME ZONE '$tz' as date) as Date,
                SUM(Duration) as ManHours
              FROM [$db].[dbo].[AgentActivity] aa WITH(NOLOCK)
              WHERE aa.GroupId = :group_id1$i
              AND aa.Date >= :startdate1$i
              AND aa.Date < :enddate1$i
              AND [Action] <> 'Paused'";

            if (session('ssoRelativeCampaigns', 0)) {
                $sql .= " AND aa.Campaign IN (SELECT CampaignName FROM dbo.GetAllRelativeCampaigns(:ssousercamp2$i, 1))";
                $bind['ssousercamp2' . $i] = session('ssoUsername');
            }

            if (session('ssoRelativeReps', 0)) {
                $sql .= " AND aa.Rep IN (SELECT RepName FROM dbo.GetAllRelativeReps(:ssouserrep2$i))";
                $bind['ssouserrep2' . $i] = session('ssoUsername');
            }

            $sql .= "
              GROUP BY Campaign, IsNull(Subcampaign, ''), CAST(CONVERT(datetimeoffset, Date) AT TIME ZONE '$tz' as date)";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp
            GROUP BY Campaign, Subcampaign, Date
			) a
        WHERE #SubcampaignSummary.Campaign = a.Campaign
        AND #SubcampaignSummary.Subcampaign = a.Subcampaign
        AND #SubcampaignSummary.Date = a.Date;";

        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id2' . $i] = Auth::user()->group_id;

            $sql .= "UPDATE #SubcampaignSummary
            SET Total += a.Total
            FROM (SELECT l.Campaign, IsNull(l.Subcampaign, '') as Subcampaign, COUNT(l.id) as Total
                FROM [$db].[dbo].[Leads] l WITH(NOLOCK)
                WHERE l.GroupId = :group_id2$i
                GROUP BY l.Campaign, IsNull(l.Subcampaign, '')) a
            WHERE #SubcampaignSummary.Campaign = a.Campaign
            AND #SubcampaignSummary.Subcampaign = a.Subcampaign;";
        }

        $sql .= "
        UPDATE #SubcampaignSummary
        SET AvAttempt = a.AvAttempt FROM (
            SELECT
              Campaign,
              Subcampaign,
              AVG(Attempt) as AvAttempt,
              Date
            FROM (";

        $union = '';
        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id3' . $i] = Auth::user()->group_id;
            $bind['startdate3' . $i] = $startDate;
            $bind['enddate3' . $i] = $endDate;

            $sql .= " $union SELECT l.Campaign, IsNull(l.Subcampaign, '') as Subcampaign, l.Attempt, CAST(CONVERT(datetimeoffset, LastUpdated) AT TIME ZONE '$tz' as date) as Date
            FROM [$db].[dbo].[Leads] l WITH(NOLOCK)
            WHERE l.GroupId = :group_id3$i
            AND l.LastUpdated >= :startdate3$i
            AND l .LastUpdated < :enddate3$i";

            $union = 'UNION ALL';
        }
        $sql .= ") tmp
        GROUP BY Campaign, Subcampaign, Date
        ) a
        WHERE #SubcampaignSummary.Campaign = a.Campaign
        AND #SubcampaignSummary.Subcampaign = a.Subcampaign
        AND #SubcampaignSummary.Date = a.Date

        UPDATE #SubcampaignSummary
        SET Available = (a.Available/CAST(#SubcampaignSummary.Total as numeric(18,2))) * 100
        FROM (
            SELECT Campaign, Subcampaign, SUM(Available) as Available FROM (";

        $union = '';
        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id4' . $i] = Auth::user()->group_id;

            $sql .= " $union SELECT
                  l.Campaign,
                  IsNull(l.Subcampaign, '') as Subcampaign,
                  COUNT(l.id) as Available
                FROM [$db].[dbo].[Leads] l WITH(NOLOCK)
                LEFT JOIN #DialingSettings ds on ds.Campaign = l.Campaign
                WHERE l.GroupId = :group_id4$i
                AND l.WasDialed = 0
                AND (ds.MaxDialingAttempts = 0 OR l.Attempt < ds.MaxDialingAttempts)
                GROUP BY l.Campaign, IsNull(l.Subcampaign, '')";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp
          GROUP BY Campaign, Subcampaign) a
        WHERE #SubcampaignSummary.Campaign = a.Campaign
        AND #SubcampaignSummary.Subcampaign = a.Subcampaign
        AND #SubcampaignSummary.Total > 0

        UPDATE #SubcampaignSummary
        SET Dialed = a.Dialed
        FROM (SELECT Campaign, Subcampaign, SUM([Count]) as Dialed, Date
              FROM #DialingResultsStats WITH(NOLOCK)
              GROUP BY Date, Campaign, Subcampaign) a
        WHERE #SubcampaignSummary.Campaign = a.Campaign
        AND #SubcampaignSummary.Subcampaign = a.Subcampaign
        AND #SubcampaignSummary.Date = a.Date

        UPDATE #SubcampaignSummary
        SET CPH = CAST(Connects as numeric(18,2))/ManHours,
            APH = CAST(Sales as numeric(18,2))/ManHours,
            DPH = CAST(Dialed as numeric(18,2))/ManHours
        WHERE ManHours > 0

        UPDATE #SubcampaignSummary
        SET ConversionFactor = (CAST(Sales as numeric(18,2)) /CAST(Dialed as numeric(18,2))) / ManHours
        WHERE ManHours > 0 AND Dialed > 0

        UPDATE #SubcampaignSummary
        SET ConnectRate = (CAST(Connects as numeric(18,2))/CAST(Dialed as numeric(18,2))) * 100,
            ConversionRate = (CAST(Sales as numeric(18,2)) / CAST(Dialed as numeric(18,2))) * 100,
            CeptsPercentage = (CAST(Cepts as numeric(18,2)) / CAST(Dialed as numeric(18,2))) * 100
        WHERE Dialed > 0

        UPDATE #SubcampaignSummary
        SET SaleRateValue = CAST(Dialed as numeric(18,2))/CAST(Sales as numeric(18,2))
        WHERE Sales > 0

        UPDATE #SubcampaignSummary
        SET TalkTimeCount = a.tot
        FROM (SELECT aa.Campaign, aa.Subcampaign, COUNT(*) as tot
              FROM #AgentSummaryDuration aa WITH(NOLOCK)
              WHERE aa.Action in ('Call', 'ManualCall', 'InboundCall')
              GROUP BY aa.Campaign, aa.Subcampaign) a
        WHERE #SubcampaignSummary.Campaign = a.Campaign
        AND #SubcampaignSummary.Subcampaign = a.Subcampaign;

        UPDATE #SubcampaignSummary
        SET ThresholdCalls = a.tot
        FROM (SELECT aa.Campaign, aa.Subcampaign, Count(*) as tot
              FROM #AgentSummaryDuration aa WITH(NOLOCK)
              WHERE aa.Duration >= :threshold_secs1
              AND aa.Action in ('Call', 'ManualCall', 'InboundCall')
              GROUP BY aa.Campaign, aa.Subcampaign) a
        WHERE #SubcampaignSummary.Campaign = a.Campaign
        AND #SubcampaignSummary.Subcampaign = a.Subcampaign;

        UPDATE #SubcampaignSummary
        SET ThresholdRatio = (CAST(ThresholdCalls as numeric(18,2)) / CAST(TalkTimeCount as numeric(18,2))) * 100
        WHERE TalkTimeCount > 0;

        UPDATE #SubcampaignSummary
        SET ThresholdSales = a.cnt
        FROM (SELECT D.Campaign, D.Subcampaign, COUNT(*) as cnt
            FROM #AgentSummaryDuration D
            INNER JOIN #Sales S on S.Rep = D.Rep AND S.ActivityId = D.ActivityId
            AND D.Duration >= :threshold_secs2
            GROUP BY D.Campaign, D.Subcampaign) a
        WHERE #SubcampaignSummary.Campaign = a.Campaign
        AND #SubcampaignSummary.Subcampaign = a.Subcampaign;

        UPDATE #SubcampaignSummary
        SET ThresholdClosingPct = CAST(ThresholdSales as numeric(18,2)) / CAST(Sales as numeric(18,2)) * 100
        WHERE Sales > 0;

        SELECT *, totRows = COUNT(*) OVER()
        FROM #SubcampaignSummary
        ORDER BY Date, Campaign, Subcampaign";

        if (!$all) {
            $offset = ($this->params['curpage'] - 1) * $this->params['pagesize'];
            $sql .= " OFFSET $offset ROWS FETCH NEXT " . $this->params['pagesize'] . " ROWS ONLY";
        }

        return [$sql, $bind];
    }

    public function processRow($rec)
    {
        array_pop($rec);
        $rec['Available'] .= '%';
        $rec['ConnectRate'] .= '%';
        $rec['ConversionRate'] .= '%';
        $rec['ThresholdRatio'] .= '%';
        $rec['ThresholdClosingPct'] .= '%';
        $rec['Date'] = Carbon::parse(($rec['Date']))->isoFormat('L');

        unset($rec['CeptsPercentage']);
        unset($rec['TalkTimeCount']);
        unset($rec['ThresholdSales']);

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
