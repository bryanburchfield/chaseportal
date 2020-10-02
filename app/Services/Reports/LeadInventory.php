<?php

namespace App\Services\Reports;

use App\Traits\CampaignTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\ReportTraits;
use Illuminate\Support\Facades\Log;

class LeadInventory
{
    use ReportTraits;
    use CampaignTraits;

    public function __construct()
    {
        $this->initilaizeParams(false);

        $this->params['reportName'] = 'reports.lead_inventory';
        $this->params['datesOptional'] = true;
        $this->params['campaigns'] = [];
        $this->params['columns'] = [
            'Description' => 'reports.resultcodes',
            'Type' => 'reports.type',
            'Leads' => 'reports.count',
        ];
    }

    public function getFilters()
    {
        $filters = [
            'campaigns' => $this->getAllCampaigns(),
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

        Log::debug($sql);
        Log::debug($bind);

        $results = $this->runSql($sql, $bind);

        if (empty($results)) {
            $this->params['totrows'] = 0;
            $this->params['totpages'] = 1;
            $this->params['curpage'] = 1;
            $results = [];
        } else {
            $this->params['totrows'] = $results[0]['totRows'];
            $this->extras['TotalLeads'] = $results[0]['TotalLeads'];
            $this->extras['AvailableLeads'] = $results[0]['AvailableLeads'];

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

        if (empty($this->params['fromdate']) || empty($this->params['todate'])) {
            $startDate = '1900-01-01 00:00:00';
            $endDate = date('Y-m-d H:i:s');
        } else {
            list($fromDate, $toDate) = $this->dateRange($this->params['fromdate'], $this->params['todate']);

            // convert to datetime strings
            $startDate = $fromDate->format('Y-m-d H:i:s');
            $endDate = $toDate->format('Y-m-d H:i:s');
        }

        if (!empty($this->params['campaigns'])) {
            $campaigns = str_replace("'", "''", implode('!#!', $this->params['campaigns']));
        }

        $bind['group_id'] = Auth::user()->group_id;
        $bind['campaigns'] = $campaigns;

        $sql = "SET NOCOUNT ON;

        DECLARE @MaxDialingAttempts int;

        SET @MaxDialingAttempts = dbo.GetGroupCampaignSetting(:group_id, '', 'MaxDialingAttempts', 0) -- unlimited

        CREATE TABLE #SelectedCampaign(CampaignName varchar(50) Primary Key)
        INSERT INTO #SelectedCampaign SELECT DISTINCT [value] from dbo.SPLIT(:campaigns, '!#!')

        CREATE TABLE #ShiftReport(
            CallStatus varchar(50),
            IsCallable bit DEFAULT 0,
            WasDialed bit,
            [Description] varchar(255),
            [Type] varchar(50),
            Leads int default 0,
            TotalLeads int default 0,
            AvailableLeads int default 0,
        )

        CREATE UNIQUE INDEX IX_CampaignRep ON #ShiftReport (CallStatus, WasDialed);

        SELECT * INTO #LeadCounts FROM (";

        $union = '';
        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id' . $i] = Auth::user()->group_id;
            $bind['startdate' . $i] = $startDate;
            $bind['enddate' . $i] = $endDate;

            $sql .= " $union SELECT
            CASE IsNull(dr.CallStatus, '')
                WHEN '' THEN '[ Not Called ]'
                ELSE dr.CallStatus
            END as CallStatus,
            IsNull(DI.IsCallable, 0) as IsCallable,
            WasDialed,
            DI.Description,
            CASE IsNull(DI.[Type], 0)
                WHEN 0 THEN 'No Connect'
                WHEN 1 THEN 'Connect'
                WHEN 2 THEN 'Contact'
                WHEN 3 THEN 'Lead/Sale'
            END as [Type],
            COUNT(dr.CallStatus) as Leads
            FROM [$db].[dbo].[Leads] dr WITH(NOLOCK)
            LEFT JOIN  [$db].[dbo].[Dispos] DI on DI.id = dr.DispositionId
            INNER JOIN #SelectedCampaign c on c.CampaignName = dr.Campaign
            WHERE dr.GroupId = :group_id$i
            AND dr.Date >= :startdate$i
            AND dr.Date < :enddate$i
            AND CallStatus not in ('CR_CNCT/CON_CAD', 'CR_CNCT/CON_PVD')
            GROUP BY dr.CallStatus, DI.isCallable, dr.WasDialed, DI.Description, DI.Type, dr.GroupId";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp

        INSERT INTO #ShiftReport(CallStatus, IsCallable, WasDialed, [Description], [Type], Leads)
		SELECT CallStatus, IsCallable, WasDialed, [Description], [Type], SUM(Leads)
		FROM #LeadCounts
		GROUP BY CallStatus, IsCallable, WasDialed, [Description], [Type]

        UPDATE #ShiftReport
        SET TotalLeads = a.Leads
        FROM (SELECT SUM(Leads) as Leads FROM #ShiftReport) a";

        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id1' . $i] = Auth::user()->group_id;

            $sql .= "
            UPDATE #ShiftReport
            SET AvailableLeads += a.Leads
            FROM (SELECT COUNT(DISTINCT l.id) as Leads
                FROM [$db].[dbo].[Leads] l WITH(NOLOCK)
                LEFT JOIN dialer_DialingSettings ds on ds.GroupId = l.GroupId and ds.Campaign = l.Campaign and ds.Subcampaign = l.Subcampaign
                LEFT JOIN dialer_DialingSettings ds2 on ds2.GroupId = l.GroupId and ds2.Campaign = l.Campaign
                INNER JOIN #SelectedCampaign c on c.CampaignName = l.Campaign
                WHERE l.GroupId = :group_id1$i
                AND (IsNull(ds.MaxDialingAttempts, IsNull(ds2.MaxDialingAttempts, @MaxDialingAttempts)) <> 0
                AND l.Attempt < IsNull(ds.MaxDialingAttempts, IsNull(ds2.MaxDialingAttempts, @MaxDialingAttempts)))
                AND l.WasDialed = 0
            ) a";
        }

        $sql .= "
        UPDATE #ShiftReport
        SET [Description] = CallStatus
        WHERE IsNull([Description], '') = ''

        UPDATE #ShiftReport
        SET IsCallable = 1
        WHERE CallStatus in ('[ Not Called ]', 'AGENTSPCB', 'SYS_CALLBACK')

        SELECT
            [Description],
            [Type],
            SUM(Leads) as Leads,
            TotalLeads,
            AvailableLeads,
            totRows = COUNT(*) OVER()
        FROM #ShiftReport
        GROUP BY [Description], [Type], TotalLeads, AvailableLeads, IsCallable";

        // Check params
        if (!empty($this->params['orderby']) && is_array($this->params['orderby'])) {
            $sort = '';
            foreach ($this->params['orderby'] as $col => $dir) {
                $sort .= ",$col $dir";
            }
            $sql .= ' ORDER BY ' . substr($sort, 1);
        } else {
            $sql .= ' ORDER BY IsCallable DESC, [Description]';
        }

        if (!$all) {
            $offset = ($this->params['curpage'] - 1) * $this->params['pagesize'];
            $sql .= " OFFSET $offset ROWS FETCH NEXT " . $this->params['pagesize'] . " ROWS ONLY";
        }

        return [$sql, $bind];
    }

    public function processRow($rec)
    {
        array_pop($rec);
        array_pop($rec);
        array_pop($rec);

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

        if (!empty($request->campaigns)) {
            $this->params['campaigns'] = $request->campaigns;
        } else {
            $this->errors->add('campaigns.required', trans('reports.errcampaignrequired'));
        }

        // Save params to session
        $this->saveSessionParams();

        return $this->errors;
    }
}
