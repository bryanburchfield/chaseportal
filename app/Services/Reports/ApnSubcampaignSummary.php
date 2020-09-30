<?php

namespace App\Services\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\ReportTraits;
use Illuminate\Support\Carbon;

class ApnSubcampaignSummary
{
    use ReportTraits;

    private $sameDay = false;

    public function __construct()
    {
        $this->initilaizeParams();

        $this->params['reportName'] = 'reports.subcampaign_summary';
        $this->params['threshold_secs'] = 0;
        $this->params['columns'] = [
            'Campaign' => 'reports.campaign',
            'Subcampaign' => 'reports.subcampaign',
            'Date' => 'reports.date',
            'Total' => 'reports.total_leads',
            'Dialed' => 'reports.dialed',
            'DPH' => 'reports.dph',
            'AvailablePct' => 'reports.available',
            'AvAttempt' => 'reports.avattempt',
            'ManHours' => 'reports.manhours',
            'Connects' => 'reports.connects',
            'Contacts' => 'reports.contacts',
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
        // Check if we're running for a single day
        $from = Carbon::parse($this->params['fromdate']);
        $to = Carbon::parse($this->params['todate']);

        if ($from->isSameDay($to)) {
            $this->sameDay = true;
        }

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

        $tz =  Auth::user()->tz;

        $bind['group_id'] = Auth::user()->group_id;

        $sql = "SET NOCOUNT ON;

        CREATE TABLE #SubcampaignSummary(
            Campaign varchar(50),
            Subcampaign varchar(50),
            Date varchar(50),
            Total int DEFAULT 0,
            Dialed int DEFAULT 0,
            DPH numeric(18,2) DEFAULT 0,
            Available int DEFAULT 0,
            AvailablePct numeric(18,2) DEFAULT 0,
            AvAttempt int DEFAULT 0,
            TotAttempt int DEFAULT 0,
            CountAttempt int DEFAULT 0,
            ManHours numeric(18,2) DEFAULT 0,
            Connects int DEFAULT 0,
            Contacts int DEFAULT 0,
            CPH numeric(18,2) DEFAULT 0,
            Sales int DEFAULT 0,
            APH numeric(18,2) DEFAULT 0,
            ConnectRate numeric(18,2) DEFAULT 0,
            SaleRateValue numeric(18,2) DEFAULT 0,
            ConversionRate numeric(18,2) DEFAULT 0,
            ConversionFactor numeric(18,2) DEFAULT 0,
            Cepts int DEFAULT 0,
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
            $bind['threshold' . $i] = $this->params['threshold_secs'];

            $sql .= " $union SELECT Campaign, Subcampaign, Date, CallStatus, [Type], SUM(OverThreshold) as OverThreshold, COUNT(*) as [Count]
            FROM ( SELECT
              dr.Campaign,
              IsNull(dr.Subcampaign, '') as Subcampaign,
              CAST(CONVERT(datetimeoffset, Date) AT TIME ZONE '$tz' as date) as Date,
              dr.CallStatus as CallStatus,
              IsNull(Di.Type,0) as [Type],
              CASE WHEN dr.Duration >= :threshold$i THEN 1 ELSE 0 END as OverThreshold
            FROM [$db].[dbo].[DialingResults] dr WITH(NOLOCK)
            LEFT JOIN [$db].[dbo].[Dispos] DI ON DI.id = dr.DispositionId
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

            $sql .= ") a
            GROUP BY Campaign, Subcampaign, Date, CallStatus, [Type]";

            $union = 'UNION ALL';
        }

        $sql .=
            ") tmp;

        CREATE INDEX IX_CampaignType ON #DialingResultsStats (Campaign, Subcampaign, [Type], Date);
        CREATE INDEX IX_Type ON #DialingResultsStats ([Type], Date);

        SELECT * INTO #AgentSummaryDuration FROM (";

        $union = '';
        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id_dur' . $i] =  Auth::user()->group_id;
            $bind['startdate_dur' . $i] = $startDate;
            $bind['enddate_dur' . $i] = $endDate;

            $sql .= " $union SELECT
            CAST(CONVERT(datetimeoffset, aa.Date) AT TIME ZONE '$tz' as date) as Date,
            aa.Rep, [Action], aa.Duration, aa.ActivityId, aa.Campaign, aa.Subcampaign
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

        CREATE INDEX IX_ASCampaignType ON #AgentSummaryDuration (Date, Campaign, Subcampaign);

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
        SET Contacts = a.Contacts
        FROM (SELECT Campaign, Subcampaign, SUM([Count]) as Contacts, Date
              FROM #DialingResultsStats
              WHERE [Type] > 1
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
        FROM (SELECT Campaign, Subcampaign, SUM([Count]) as Cepts, Date
              FROM #DialingResultsStats
              WHERE CallStatus = 'CR_CEPT'
              GROUP BY Campaign, Subcampaign, Date) a
        WHERE #SubcampaignSummary.Campaign = a.Campaign
        AND #SubcampaignSummary.Subcampaign = a.Subcampaign
        AND #SubcampaignSummary.Date = a.Date

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
        SET AvAttempt = a.AvAttempt,
          TotAttempt = a.TotAttempt,
          CountAttempt = a.CountAttempt
        FROM (
            SELECT
              Campaign,
              Subcampaign,
              AVG(Attempt) as AvAttempt,
              SUM(Attempt) as TotAttempt,
              COUNT(Attempt) as CountAttempt,
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
        SET AvailablePct = (a.Available/CAST(#SubcampaignSummary.Total as numeric(18,2))) * 100,
          Available = a.Available
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
        SET ConnectRate = (CAST(Connects as numeric(18,2))/CAST(Dialed as numeric(18,2))) * 100
        WHERE Dialed > 0

        UPDATE #SubcampaignSummary
        SET ConversionRate = (CAST(Sales as numeric(18,2)) / CAST(Contacts as numeric(18,2))) * 100
        WHERE Contacts > 0

        UPDATE #SubcampaignSummary
        SET SaleRateValue = CAST(Contacts as numeric(18,2))/CAST(Sales as numeric(18,2))
        WHERE Sales > 0

        UPDATE #SubcampaignSummary
        SET TalkTimeCount = a.tot
        FROM (SELECT aa.Campaign, aa.Subcampaign, COUNT(*) as tot, Date
              FROM #AgentSummaryDuration aa WITH(NOLOCK)
              WHERE aa.Action in ('Call', 'ManualCall', 'InboundCall')
              GROUP BY aa.Campaign, aa.Subcampaign, Date) a
        WHERE #SubcampaignSummary.Campaign = a.Campaign
        AND #SubcampaignSummary.Subcampaign = a.Subcampaign
        AND #SubcampaignSummary.Date = a.Date;

        UPDATE #SubcampaignSummary
        SET ThresholdCalls = a.tot
        FROM (SELECT Campaign, Subcampaign, Date, SUM([OverThreshold]) as tot
              FROM #DialingResultsStats
              WHERE Type > 0
              GROUP BY Campaign, Subcampaign, Date) a
        WHERE #SubcampaignSummary.Campaign = a.Campaign
        AND #SubcampaignSummary.Subcampaign = a.Subcampaign
        AND #SubcampaignSummary.Date = a.Date;

        UPDATE #SubcampaignSummary
        SET ThresholdRatio = (CAST(ThresholdCalls as numeric(18,2)) / CAST(Dialed as numeric(18,2))) * 100
        WHERE Dialed > 0;

        UPDATE #SubcampaignSummary
        SET ThresholdSales = a.tot
        FROM (SELECT Campaign, Subcampaign, Date, SUM([OverThreshold]) as tot
              FROM #DialingResultsStats
              WHERE Type = 3
              GROUP BY Campaign, Subcampaign, Date) a
        WHERE #SubcampaignSummary.Campaign = a.Campaign
        AND #SubcampaignSummary.Subcampaign = a.Subcampaign
        AND #SubcampaignSummary.Date = a.Date;

        UPDATE #SubcampaignSummary
        SET ThresholdClosingPct = CAST(ThresholdSales as numeric(18,2)) / CAST(ThresholdCalls as numeric(18,2)) * 100
        WHERE ThresholdCalls > 0;

        SELECT *
        FROM #SubcampaignSummary
        ORDER BY Campaign, Subcampaign, Date";

        return [$sql, $bind];
    }

    private function processResults($results)
    {
        // this sets the order of the columns
        foreach ($this->params['columns'] as $k => $v) {
            $subtotal[$k] = '';
        }

        $subtotal = $this->zeroRec($subtotal);

        $oldgroup = '';

        $final = [];
        foreach ($results as $rec) {
            if (!$this->sameDay) {
                $thisgroup = $rec['Campaign'] . $rec['Subcampaign'];

                if ($thisgroup != $oldgroup && $oldgroup != '') {
                    $final[] = $this->processSubTotal($subtotal);
                    $subtotal = $this->zeroRec($subtotal);
                }

                $subtotal = $this->addTotals($subtotal, $rec);
                $oldgroup = $thisgroup;
            }

            $final[] = $this->processRow($rec);
        }

        if (!$this->sameDay) {
            if (count($final)) {
                $final[] = $this->processSubTotal($subtotal);
            }
        }

        return $final;
    }

    public function processRow($rec)
    {
        $rec['AvailablePct'] .= '%';
        $rec['ConnectRate'] .= '%';
        $rec['ConversionRate'] .= '%';
        $rec['ThresholdRatio'] .= '%';
        $rec['ThresholdClosingPct'] .= '%';
        $rec['Date'] = Carbon::parse(($rec['Date']))->isoFormat('L');

        // remove count cols
        unset($rec['Available']);
        unset($rec['CountAttempt']);
        unset($rec['DispositionTimeCount']);
        unset($rec['TalkTimeCount']);
        unset($rec['ThresholdSales']);
        unset($rec['TotAttempt']);

        return $rec;
    }

    private function zeroRec($rec)
    {
        $rec['APH'] = 0;
        $rec['AvAttempt'] = 0;
        $rec['Available'] = 0;
        $rec['AvailablePct'] = 0;
        $rec['CPH'] = 0;
        $rec['Cepts'] = 0;
        $rec['ConnectRate'] = 0;
        $rec['Connects'] = 0;
        $rec['Contacts'] = 0;
        $rec['ConversionFactor'] = 0;
        $rec['ConversionRate'] = 0;
        $rec['CountAttempt'] = 0;
        $rec['DPH'] = 0;
        $rec['Dialed'] = 0;
        $rec['ManHours'] = 0;
        $rec['SaleRateValue'] = 0;
        $rec['Sales'] = 0;
        $rec['TalkTimeCount'] = 0;
        $rec['ThresholdCalls'] = 0;
        $rec['ThresholdClosingPct'] = 0;
        $rec['ThresholdRatio'] = 0;
        $rec['ThresholdSales'] = 0;
        $rec['TotAttempt'] = 0;
        $rec['Total'] = 0;

        return $rec;
    }

    private function addTotals($total, $rec)
    {
        // Available and total leads never change, so only set once
        if ($total['Available'] == 0) {
            $total['Available'] = $rec['Available'];
        }
        if ($total['Total'] == 0) {
            $total['Total'] = $rec['Total'];
        }

        $total['Cepts'] += $rec['Cepts'];
        $total['Connects'] += $rec['Connects'];
        $total['Contacts'] += $rec['Contacts'];
        $total['CountAttempt'] += $rec['CountAttempt'];
        $total['Dialed'] += $rec['Dialed'];
        $total['ManHours'] += $rec['ManHours'];
        $total['Sales'] += $rec['Sales'];
        $total['TalkTimeCount'] += $rec['TalkTimeCount'];
        $total['ThresholdCalls'] += $rec['ThresholdCalls'];
        $total['ThresholdSales'] += $rec['ThresholdSales'];
        $total['TotAttempt'] += $rec['TotAttempt'];

        return $total;
    }

    private function processSubTotal($rec)
    {
        if ($rec['Total'] > 0) {
            $rec['AvailablePct'] = $rec['Available'] / $rec['Total'] * 100;
        }

        if ($rec['ManHours'] > 0) {
            $rec['CPH'] = $rec['Connects'] / $rec['ManHours'];
            $rec['APH'] = $rec['Sales'] / $rec['ManHours'];
            $rec['DPH'] = $rec['Dialed'] / $rec['ManHours'];

            if ($rec['Dialed'] > 0) {
                $rec['ConversionFactor'] = $rec['Sales'] / $rec['Dialed'] / $rec['ManHours'];
            }
        }

        if ($rec['Dialed'] > 0) {
            $rec['ConnectRate'] = $rec['Connects'] / $rec['Dialed'] * 100;
        }

        if ($rec['Contacts'] > 0) {
            $rec['ConversionRate'] = $rec['Sales'] / $rec['Contacts'] * 100;
        }

        if ($rec['Sales'] > 0) {
            $rec['SaleRateValue'] = $rec['Dialed'] / $rec['Sales'];
        }

        if ($rec['CountAttempt'] > 0) {
            $rec['AvAttempt'] = $rec['TotAttempt'] / $rec['CountAttempt'];
        }

        $rec['ThresholdRatio'] = $rec['Dialed'] == 0 ? 0 : $rec['ThresholdCalls'] / $rec['Dialed'] * 100;
        $rec['ThresholdClosingPct'] = $rec['ThresholdCalls'] == 0 ? 0 : $rec['ThresholdSales'] / $rec['ThresholdCalls'] * 100;

        // format cols
        $rec['APH'] = number_format($rec['APH'], 2);
        $rec['AvAttempt'] = number_format($rec['AvAttempt'], 0);
        $rec['AvailablePct'] = number_format($rec['AvailablePct'], 2) . '%';
        $rec['CPH'] = number_format($rec['CPH'], 2);
        $rec['ConnectRate'] = number_format($rec['ConnectRate'], 2) . '%';
        $rec['ConversionFactor'] = number_format($rec['ConversionFactor'], 2);
        $rec['ConversionRate'] = number_format($rec['ConversionRate'], 2) . '%';
        $rec['DPH'] = number_format($rec['DPH'], 2);
        $rec['SaleRateValue'] = number_format($rec['SaleRateValue'], 2);
        $rec['ThresholdClosingPct'] = number_format($rec['ThresholdClosingPct'], 2) . '%';
        $rec['ThresholdRatio'] = number_format($rec['ThresholdRatio'], 2) . '%';

        // remove count cols
        unset($rec['Available']);
        unset($rec['CountAttempt']);
        unset($rec['DispositionTimeCount']);
        unset($rec['TalkTimeCount']);
        unset($rec['ThresholdSales']);
        unset($rec['TotAttempt']);

        if (!$this->export) {
            $rec['isSubtotal'] = 1;
        }

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
