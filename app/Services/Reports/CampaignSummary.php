<?php

namespace App\Services\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;


class CampaignSummary
{
    use ReportTraits;

    public function __construct()
    {
        $this->initilaizeParams();

        $this->params['reportName'] = 'Campaign Summary Report';
        $this->params['fromdate'] = '';
        $this->params['todate'] = '';
        $this->params['columns'] = [
            'Campaign' => 'Campaign',
            'Total' => 'Total',
            'Dialed' => 'Dialed',
            'DPH' => 'Dials per Hr',
            'Available' => 'Available',
            'AvAttempt' => 'Avg Attempts',
            'ManHours' => 'Man Hours',
            'Connects' => 'Connects',
            'CPH' => 'Connects per Hr',
            'ConversionRate' => 'Conversion Rate',
            'ConversionFactor' => 'Conversion Factor',
            'Leads' => 'Sale/Lead/App',
            'SPH' => 'S-L-A/HR',
            'DropCallsPercentage' => 'Drop Rate (Connected Calls)',
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

        $bind['group_id1'] =  Auth::user()->group_id;
        $bind['group_id2'] =  Auth::user()->group_id;
        $bind['group_id3'] =  Auth::user()->group_id;
        $bind['group_id4'] =  Auth::user()->group_id;
        $bind['group_id5'] =  Auth::user()->group_id;
        $bind['group_id6'] =  Auth::user()->group_id;
        $bind['group_id7'] =  Auth::user()->group_id;
        $bind['startdate1'] = $startDate;
        $bind['startdate2'] = $startDate;
        $bind['startdate3'] = $startDate;
        $bind['enddate1'] = $endDate;
        $bind['enddate2'] = $endDate;
        $bind['enddate3'] = $endDate;

        $sql = "SET NOCOUNT ON;
    
    CREATE TABLE #CampaignSummary(
        Campaign varchar(50),
        Total int DEFAULT 0,
        Dialed int DEFAULT 0,
        DPH numeric(18,2) DEFAULT 0,
        Available numeric(18,2) DEFAULT 0,
        AvAttempt int DEFAULT 0,
        ManHours numeric(18,2) DEFAULT 0,
        Connects int DEFAULT 0,
        CPH numeric(18,2) DEFAULT 0,
        ConversionRate numeric(18,2) DEFAULT 0,
        ConversionFactor numeric(18,2) DEFAULT 0,
        Sales int DEFAULT 0,
        SPH numeric(18,2) DEFAULT 0,
        DropCallsPercentage numeric(18,2) DEFAULT 0,
        Dropped numeric(18,2) DEFAULT 0,
    );
    
    CREATE UNIQUE INDEX IX_CampaignDate ON #CampaignSummary (Campaign);
    
    SELECT * INTO #DialingResultsStats FROM (";

        $union = '';
        foreach (Auth::user()->getDatabaseArray() as $db) {
            $sql .= " $union SELECT
        dr.Campaign as Campaign,
        dr.CallStatus as CallStatus,
        IsNull((SELECT TOP 1 [Type]	FROM Dispos WHERE Disposition=dr.CallStatus AND (GroupId=:group_id1 OR IsSystem=1) AND (Campaign=dr.Campaign OR Campaign='') ORDER BY [Description] Desc), 0) as [Type],
        count(dr.CallStatus) as [Count]
        FROM [$db].[dbo].[DialingResults] dr WITH(NOLOCK)
        WHERE dr.GroupId = :group_id2
        AND dr.Date >= :startdate1
        AND dr.Date < :enddate1
        AND dr.Campaign <> '_MANUAL_CALL_'
        AND IsNull(CallStatus, '') <> ''
        AND CallStatus not in ('CR_CNCT/CON_CAD', 'CR_CNCT/CON_PVD')
        GROUP BY dr.Campaign, dr.CallStatus";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp;
    
    CREATE INDEX IX_CampaignType ON #DialingResultsStats (Campaign, [Type]);
    CREATE INDEX IX_Type ON #DialingResultsStats ([Type]);
    
    INSERT #CampaignSummary(Campaign)
    SELECT DISTINCT Campaign
    FROM #DialingResultsStats;
    
    CREATE TABLE #DialingSettings
    (
        Campaign varchar(50),
        MaxDialingAttempts int
    );
    
    INSERT INTO #DialingSettings(Campaign, MaxDialingAttempts)
    SELECT Campaign, dbo.GetGroupCampaignSetting(:group_id3, Campaign, 'MaxDialingAttempts', 0)
    FROM #CampaignSummary
    GROUP BY Campaign;
    
    UPDATE #CampaignSummary
    SET Connects = a.Connects
    FROM (SELECT Campaign, SUM([Count]) as Connects
          FROM #DialingResultsStats
          WHERE [Type] > 0
          GROUP BY Campaign) a
    WHERE #CampaignSummary.Campaign = a.Campaign;
    
    UPDATE #CampaignSummary
    SET Sales = a.Sales
    FROM (SELECT Campaign, SUM([Count]) as Sales
          FROM  #DialingResultsStats
          WHERE [Type] = 3
          GROUP BY Campaign) a
    WHERE #CampaignSummary.Campaign = a.Campaign;";

        foreach (Auth::user()->getDatabaseArray() as $db) {

            $sql .= "
        UPDATE #CampaignSummary
        SET ManHours += IsNull(a.ManHours/3600, 0)
        FROM (SELECT Campaign, SUM(Duration) as ManHours
            FROM  [$db].[dbo].[AgentActivity] aa WITH(NOLOCK)
            WHERE aa.GroupId = :group_id4
            AND aa.Date >= :startdate2
            AND aa.Date < :enddate2
            AND [Action] <> 'Paused'
            GROUP BY Campaign) a
        WHERE #CampaignSummary.Campaign = a.Campaign;
 
        UPDATE #CampaignSummary
        SET Total += a.Total
        FROM (SELECT l.Campaign, COUNT(l.id) as Total
            FROM [$db].[dbo].[Leads] l WITH(NOLOCK)
            WHERE l.GroupId = :group_id5
            GROUP BY l.Campaign) a
        WHERE #CampaignSummary.Campaign = a.Campaign;";
        }

        $sql .= "
    UPDATE #CampaignSummary
    SET Available = (a.Available/CAST(#CampaignSummary.Total as numeric(18,2))) * 100
    FROM (
        SELECT Campaign, SUM(Available) as Available FROM (";

        $union = '';
        foreach (Auth::user()->getDatabaseArray() as $db) {
            $sql .= " $union SELECT l.Campaign, COUNT(l.id) as Available
            FROM [$db].[dbo].[Leads] l WITH(NOLOCK)
            LEFT JOIN #DialingSettings cs on cs.Campaign = l.Campaign
            WHERE l.WasDialed = 0
            AND l.GroupId = :group_id6
            AND (cs.MaxDialingAttempts = 0 OR l.Attempt < cs.MaxDialingAttempts)
            GROUP BY l.Campaign";

            $union = 'UNION ALL';
        }
        $sql .= ") tmp
    GROUP BY Campaign) a
    WHERE #CampaignSummary.Campaign = a.Campaign
    AND #CampaignSummary.Total > 0;

    UPDATE #CampaignSummary
    SET AvAttempt = a.AvAttempt
    FROM ( SELECT Campaign, AVG(Attempt) as AvAttempt FROM (";

        $union = '';
        foreach (Auth::user()->getDatabaseArray() as $db) {
            $sql .= " $union SELECT Campaign, Attempt
				FROM [$db].[dbo].[Leads] WITH(NOLOCK)
				WHERE GroupId = :group_id7
				AND LastUpdated >= :startdate3
				AND LastUpdated < :enddate3";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp
        GROUP BY Campaign
    ) a
    WHERE #CampaignSummary.Campaign = a.Campaign;

    UPDATE #CampaignSummary
    SET Dialed = a.Dialed
    FROM (SELECT Campaign, SUM([Count]) as Dialed
          FROM  #DialingResultsStats WITH(NOLOCK)
          GROUP BY Campaign) a
    WHERE #CampaignSummary.Campaign = a.Campaign;
    
    UPDATE #CampaignSummary
    SET Dropped = a.Dropped
    FROM (SELECT Campaign, SUM([Count]) as Dropped
          FROM  #DialingResultsStats
          WHERE CallStatus = 'CR_DROPPED'
          GROUP BY Campaign) a
    WHERE #CampaignSummary.Campaign = a.Campaign;
    
    UPDATE #CampaignSummary
    SET CPH = Connects/ManHours,
        SPH = Sales/ManHours,
        DPH = Dialed/ManHours
    WHERE ManHours > 0;
    
    UPDATE #CampaignSummary
    SET DropCallsPercentage = (Dropped / (Connects + Dropped)) * 100
    WHERE Connects + Dropped > 0;
    
    UPDATE #CampaignSummary
    SET ConversionRate = (CAST(Sales as numeric(18,2)) / CAST(Dialed as numeric(18,2))) * 100
    WHERE Dialed > 0;
    
    UPDATE #CampaignSummary
    SET ConversionFactor = (CAST(Sales as numeric(18,2)) /CAST(Dialed as numeric(18,2))) / ManHours
    WHERE ManHours > 0 AND Dialed > 0;
    
    SELECT 
        Campaign,
        Total,
        Dialed,
        DPH,
        Available,
        AvAttempt,
        ManHours,
        Connects,
        CPH,
        ConversionRate,
        ConversionFactor,
        Sales,
        SPH,
        DropCallsPercentage,
        totRows = COUNT(*) OVER()
    FROM #CampaignSummary";

        // Check params
        if (!empty($this->params['orderby']) && is_array($this->params['orderby'])) {
            $sort = '';
            foreach ($this->params['orderby'] as $col => $dir) {
                $sort .= ",$col $dir";
            }
            $sql .= ' ORDER BY ' . substr($sort, 1);
        } else {
            $sql .= ' ORDER BY Campaign';
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
                $rec['ConversionRate'] .= '%';
                $rec['DropCallsPercentage'] .= '%';
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