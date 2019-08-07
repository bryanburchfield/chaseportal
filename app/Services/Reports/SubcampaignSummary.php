<?php

namespace App\Services\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\ReportTraits;

class SubcampaignSummary
{
    use ReportTraits;

    public function __construct()
    {
        $this->initilaizeParams();

        $this->params['reportName'] = 'Subcampaign Summary Report';
        $this->params['fromdate'] = '';
        $this->params['todate'] = '';
        $this->params['columns'] = [
            'Date' => 'Date',
            'Campaign' => 'Campaign',
            'Subcampaign' => 'Subcampaign',
            'Total' => 'Total',
            'Dialed' => 'Dialed',
            'DPH' => 'Dials per Hr',
            'Available' => 'Available',
            'AvAttempt' => 'Attempt',
            'ManHours' => 'Man Hours',
            'Connects' => 'Connects',
            'CPH' => 'Connects per Hr',
            'Sales' => 'Sale/Lead/App',
            'SPH' => 'S-L-A per Hr',
            'ConnectRate' => 'Connect Rate',
            'SaleRateValue' => 'S-L-A Rate Value',
            'ConversionRate' => 'Conversion Rate',
            'ConversionFactor' => 'Conversion Factor',
            'Cepts' => 'Operator Disconnects',
        ];
    }

    public function getFilters()
    {
        $filters = [];

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
        $bind['group_id1'] =  Auth::user()->group_id;
        $bind['group_id2'] =  Auth::user()->group_id;
        $bind['group_id3'] =  Auth::user()->group_id;
        $bind['group_id4'] =  Auth::user()->group_id;
        $bind['group_id5'] =  Auth::user()->group_id;
        $bind['group_id6'] =  Auth::user()->group_id;
        $bind['group_id7'] =  Auth::user()->group_id;
        $bind['startdate1'] = $startDate;
        $bind['enddate1'] = $endDate;
        $bind['startdate2'] = $startDate;
        $bind['enddate2'] = $endDate;
        $bind['startdate3'] = $startDate;
        $bind['enddate3'] = $endDate;

        $sql = "SET NOCOUNT ON;

        CREATE TABLE #SubcampaignSummary(
            Date varchar(50),
            Campaign varchar(50),
            Subcampaign varchar(50),
            Total int DEFAULT 0,
            Dialed int DEFAULT 0,
            Available numeric(18,2) DEFAULT 0,
            AvAttempt int DEFAULT 0,
            ManHours numeric(18,2) DEFAULT 0,
            Connects int DEFAULT 0,
            CPH numeric(18,2) DEFAULT 0,
            Sales int DEFAULT 0,
            SPH numeric(18,2) DEFAULT 0,
            DPH numeric(18,2) DEFAULT 0,
            ConnectRate numeric(18,2) DEFAULT 0,
            SaleRateValue numeric(18,2) DEFAULT 0,
            ConversionRate numeric(18,2) DEFAULT 0,
            ConversionFactor numeric(18,2) DEFAULT 0,
            Cepts int DEFAULT 0,
            CeptsPercentage numeric(18,2) DEFAULT 0
        );

        CREATE UNIQUE INDEX IX_CampaignDate ON #SubcampaignSummary (Campaign, Subcampaign, Date);

        SELECT * INTO #DialingResultsStats FROM (";

        $union = '';
        foreach (Auth::user()->getDatabaseArray() as $db) {
            $sql .= " $union SELECT
            CAST(CONVERT(datetimeoffset, Date) AT TIME ZONE '$tz' as date) as Date,
            dr.Campaign,
            IsNull(dr.Subcampaign, '') as Subcampaign,
            dr.CallStatus as CallStatus,
            IsNull((SELECT TOP 1 [Type]
              FROM [$db].[dbo].[Dispos]
              WHERE Disposition=dr.CallStatus AND (GroupId=:group_id1 OR IsSystem=1) AND (Campaign=dr.Campaign OR IsDefault=1) AND (Campaign=dr.Campaign OR IsDefault=1) ORDER BY [Description] Desc), 0) as [Type],
            count(dr.CallStatus) as [Count]
            FROM [$db].[dbo].[DialingResults] dr WITH(NOLOCK)
            WHERE dr.GroupId = :group_id2
            AND dr.Date >= :startdate1
            AND dr.Date < :enddate1
            AND dr.Campaign <> '_MANUAL_CALL_'
            AND IsNull(dr.CallStatus, '') <> ''
            AND dr.CallStatus not in ('CR_CNCT/CON_CAD', 'CR_CNCT/CON_PVD')
            GROUP BY dr.Campaign, IsNull(dr.Subcampaign, ''), dr.CallStatus, CAST(CONVERT(datetimeoffset, Date) AT TIME ZONE '$tz' as date)";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp;

        CREATE INDEX IX_CampaignType ON #DialingResultsStats (Campaign, Subcampaign, [Type], Date);
        CREATE INDEX IX_Type ON #DialingResultsStats ([Type], Date);

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
        SELECT Campaign, dbo.GetGroupCampaignSetting(:group_id3, Campaign, 'MaxDialingAttempts', 0)
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
        foreach (Auth::user()->getDatabaseArray() as $db) {
            $sql .= " $union SELECT
                Campaign,
                IsNull(Subcampaign, '') as Subcampaign,
                CAST(CONVERT(datetimeoffset, Date) AT TIME ZONE '$tz' as date) as Date,
                SUM(Duration) as ManHours
              FROM [$db].[dbo].[AgentActivity] aa WITH(NOLOCK)
              WHERE aa.GroupId = :group_id4
              AND aa.Date >= :startdate2
              AND aa.Date < :enddate2
              AND [Action] <> 'Paused'
              GROUP BY Campaign, IsNull(Subcampaign, ''), CAST(CONVERT(datetimeoffset, Date) AT TIME ZONE '$tz' as date)";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp
            GROUP BY Campaign, Subcampaign, Date
			) a
        WHERE #SubcampaignSummary.Campaign = a.Campaign
        AND #SubcampaignSummary.Subcampaign = a.Subcampaign
        AND #SubcampaignSummary.Date = a.Date;";

        foreach (Auth::user()->getDatabaseArray() as $db) {
            $sql .= "UPDATE #SubcampaignSummary
            SET Total += a.Total
            FROM (SELECT l.Campaign, IsNull(l.Subcampaign, '') as Subcampaign, COUNT(l.id) as Total
                FROM [$db].[dbo].[Leads] l WITH(NOLOCK)
                WHERE l.GroupId = :group_id5
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
        foreach (Auth::user()->getDatabaseArray() as $db) {
            $sql .= " $union SELECT l.Campaign, IsNull(l.Subcampaign, '') as Subcampaign, l.Attempt, CAST(CONVERT(datetimeoffset, LastUpdated) AT TIME ZONE '$tz' as date) as Date
            FROM [$db].[dbo].[Leads] l WITH(NOLOCK)
            WHERE l.GroupId = :group_id6
            AND l.LastUpdated >= :startdate3
            AND l .LastUpdated < :enddate3";

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
        foreach (Auth::user()->getDatabaseArray() as $db) {
            $sql .= " $union SELECT
                  l.Campaign,
                  IsNull(l.Subcampaign, '') as Subcampaign,
                  COUNT(l.id) as Available
                FROM [$db].[dbo].[Leads] l WITH(NOLOCK)
                LEFT JOIN #DialingSettings ds on ds.Campaign = l.Campaign
                WHERE l.GroupId = :group_id7
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
            SPH = CAST(Sales as numeric(18,2))/ManHours,
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

        SELECT
            Date,
            Campaign,
            Subcampaign,
            Total,
            Dialed,
            DPH,
            Available,
            AvAttempt,
            ManHours,
            Connects,
            CPH,
            Sales,
            SPH,
            ConnectRate,
            SaleRateValue,
            ConversionRate,
            ConversionFactor,
            Cepts,
            totRows = COUNT(*) OVER()
        FROM #SubcampaignSummary";





        // Check params
        if (!empty($this->params['orderby']) && is_array($this->params['orderby'])) {
            $sort = '';
            foreach ($this->params['orderby'] as $col => $dir) {
                $sort .= ",$col $dir";
            }
            $sql .= ' ORDER BY ' . substr($sort, 1);
        } else {
            $sql .= ' ORDER BY Date, Campaign, Subcampaign';
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
                $rec['Available'] .= '%';
                $rec['ConnectRate'] .= '%';
                $rec['ConversionRate'] .= '%';
                $rec['Date'] = date('m/d/Y', strtotime($rec['Date']));
            }
            $this->params['totpages'] = floor($this->params['totrows'] / $this->params['pagesize']);
            $this->params['totpages'] += floor($this->params['totrows'] / $this->params['pagesize']) == ($this->params['totrows'] / $this->params['pagesize']) ? 0 : 1;
        }

        return $results;
    }

    private function processInput(Request $request)
    {
        // Check page filters
        $this->checkPageFilters($request);

        // Check report filters
        $this->checkDateRangeFilters($request);

        return $this->errors;
    }
}
