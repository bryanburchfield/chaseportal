<?php

namespace App\Services\Reports;

use App\Traits\CampaignTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\ReportTraits;

class LeadInventorySub
{
    use ReportTraits;
    use CampaignTraits;

    public function __construct()
    {
        $this->initilaizeParams();

        $this->params['reportName'] = 'reports.lead_inventory_sub';
        $this->params['fromdate'] = date("m/d/Y 9:00 \A\M");
        $this->params['todate'] = date("m/d/Y 8:00 \P\M");
        $this->params['campaign'] = '';
        $this->params['subcampaign'] = '';
        $this->params['columns'] = [
            'Description' => 'reports.resultcodes',
            'Type' => 'reports.type',
            'Leads' => 'reports.count',
        ];
    }

    public function getFilters()
    {
        $filters = [
            'campaign' => $this->getAllCampaigns(),
            'subcampaign' => $this->getAllSubcampaigns(),
            'db_list' => Auth::user()->getDatabaseArray(),
        ];

        $filters['campaign'] =
            ['' => 'Select One'] +
            $filters['campaign'];

        return $filters;
    }

    private function executeReport($all = false)
    {
        $this->setHeadings();

        $bind['group_id'] = Auth::user()->group_id;

        $sql = "SET NOCOUNT ON;

        DECLARE @MaxDialingAttempts int;

        SET @MaxDialingAttempts = dbo.GetGroupCampaignSetting(:group_id, '', 'MaxDialingAttempts', 0)

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
            $bind['campaign' . $i] = $this->params['campaign'];
            $bind['subcampaign' . $i] = $this->params['subcampaign'];

            $sql .= " $union SELECT
                CASE IsNull(dr.CallStatus, '')
                    WHEN '' THEN '[ Not Called ]'
                    ELSE dr.CallStatus
                END as CallStatus,
                IsNull((SELECT TOP 1 IsCallable
                        FROM [$db].[dbo].[Dispos]
                        WHERE Disposition = dr.CallStatus
                        AND Campaign = dr.Campaign
                        AND (GroupId = dr.GroupId OR IsSystem = 1)
                    ORDER BY GroupID Desc, IsSystem Desc, [Description] Desc), 0) as IsCallable,
                WasDialed,
                (SELECT TOP 1 [Description]
                FROM [$db].[dbo].[Dispos]
                WHERE Disposition = dr.CallStatus
                AND Campaign = dr.Campaign
                AND (GroupId = dr.GroupId OR IsSystem = 1)
                ORDER BY GroupID Desc, IsSystem Desc, [Description] Desc) as [Description],
                IsNull((SELECT TOP 1
                    CASE [Type]
                        WHEN 0 THEN 'No Connect'
                        WHEN 1 THEN 'Connect'
                        WHEN 2 THEN 'Contact'
                        WHEN 3 THEN 'Lead/Sale'
                    END
                    FROM [$db].[dbo].[Dispos]
                    WHERE Disposition = dr.CallStatus
                    AND Campaign = dr.Campaign
                    AND (GroupId = dr.GroupId OR IsSystem = 1)
                    ORDER BY GroupID Desc, IsSystem Desc, [Description] Desc), 'No Connect') as [Type],
                count(dr.CallStatus) as Leads
            FROM [$db].[dbo].[Leads] dr WITH(NOLOCK)
            WHERE dr.GroupId = :group_id$i
            AND dr.Campaign = :campaign$i
            AND dr.Subcampaign = :subcampaign$i
            AND CallStatus not in ('CR_CNCT/CON_CAD', 'CR_CNCT/CON_PVD')
            GROUP BY dr.CallStatus, dr.WasDialed, dr.Campaign, dr.GroupId";

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
            $bind['campaign1' . $i] = $this->params['campaign'];
            $bind['subcampaign1' . $i] = $this->params['subcampaign'];

            $sql .= "
            UPDATE #ShiftReport
            SET AvailableLeads += a.Leads
                FROM (SELECT COUNT(DISTINCT l.id) as Leads
                        FROM [$db].[dbo].[Leads] l WITH(NOLOCK)
                        LEFT JOIN dialer_DialingSettings ds on ds.GroupId = l.GroupId and ds.Campaign = l.Campaign and ds.Subcampaign = l.Subcampaign
                        LEFT JOIN dialer_DialingSettings ds2 on ds.GroupId = l.GroupId and ds.Campaign = l.Campaign
                        WHERE l.GroupId = :group_id1$i
                        AND l.Campaign = :campaign1$i
                        AND l.Subcampaign = :subcampaign1$i
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
                array_pop($rec);
                array_pop($rec);
                array_pop($rec);
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

        if (!empty($request->campaign)) {
            $this->params['campaign'] = $request->campaign;
        } else {
            $this->errors->add('campaign.required', "Campaign required");
        }

        if (!empty($request->subcampaign)) {
            $this->params['subcampaign'] = $request->subcampaign;
        }

        // Save params to session
        $this->saveSessionParams();

        return $this->errors;
    }
}
