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
        $this->initilaizeParams(false);

        $this->params['reportName'] = 'reports.lead_inventory_sub';
        $this->params['datesOptional'] = true;
        $this->params['campaign'] = '';
        $this->params['subcampaigns'] = [];
        $this->params['attemptsfrom'] = '';
        $this->params['attemptsto'] = '';
        $this->params['is_callable'] = '';
        $this->params['columns'] = [
            'Subcampaign' => 'reports.subcampaign',
            'Description' => 'reports.resultcodes',
            'Type' => 'reports.type',
            'Leads' => 'reports.count',
        ];
    }

    public function getFilters()
    {
        $filters = [
            'campaign' => $this->getAllCampaigns(),
            'subcampaigns' => [],
            'is_callable' => [
                '' => '',
                'Y' => trans('general.yes'),
                'N' => trans('general.no'),
            ],
            'db_list' => Auth::user()->getDatabaseArray(),
        ];

        $filters['campaign'] =
            ['' => trans('general.select_one')] +
            $filters['campaign'];

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

        $results = $this->processResults($sql, $bind);

        if (empty($results)) {
            $this->params['totrows'] = 0;
            $this->params['totpages'] = 1;
            $this->params['curpage'] = 1;
            $results = [];
        } else {
            $this->params['totrows'] = count($results);
            $this->params['totpages'] = floor($this->params['totrows'] / $this->params['pagesize']);
            $this->params['totpages'] += floor($this->params['totrows'] / $this->params['pagesize']) == ($this->params['totrows'] / $this->params['pagesize']) ? 0 : 1;
        }

        return $this->getPage($results, $all);
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

        $subcampaigns = str_replace("'", "''", implode('!#!', $this->params['subcampaigns']));

        $bind['group_id'] = Auth::user()->group_id;
        $bind['subcampaigns'] = $subcampaigns;

        $sql = "SET NOCOUNT ON;

        DECLARE @MaxDialingAttempts int;

        SET @MaxDialingAttempts = dbo.GetGroupCampaignSetting(:group_id, '', 'MaxDialingAttempts', 0)

        CREATE TABLE #SelectedSubcampaign(Subcampaign varchar(50) Primary Key);
        INSERT INTO #SelectedSubcampaign SELECT DISTINCT [value] from dbo.SPLIT(:subcampaigns, '!#!');

        CREATE TABLE #ShiftReport(
            Subcampaign varchar(50),
            CallStatus varchar(50),
            IsCallable bit DEFAULT 0,
            WasDialed bit,
            [Description] varchar(255),
            [Type] varchar(50),
            Leads int default 0,
            TotalLeads int default 0,
            AvailableLeads int default 0,
        )

        CREATE INDEX IX_CampaignRep ON #ShiftReport (Subcampaign, CallStatus, Description, WasDialed);

        SELECT * INTO #LeadCounts FROM (";

        $union = '';
        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id' . $i] = Auth::user()->group_id;
            $bind['campaign' . $i] = $this->params['campaign'];
            $bind['startdate' . $i] = $startDate;
            $bind['enddate' . $i] = $endDate;

            $sql .= " $union SELECT
            ISNULL(dr.Subcampaign, '') as Subcampaign,
            CASE IsNull(dr.CallStatus, '')
                WHEN '' THEN '[ Not Called ]'
                ELSE dr.CallStatus
            END as CallStatus,
            IsNull(DI.IsCallable, 0) as IsCallable,
            WasDialed,
            DI.Description,
            IsNull(
                CASE [Type]
                    WHEN 0 THEN 'No Connect'
                    WHEN 1 THEN 'Connect'
                    WHEN 2 THEN 'Contact'
                    WHEN 3 THEN 'Lead/Sale'
                END, 'No Connect') as [Type],
            COUNT(dr.CallStatus) as Leads
            FROM [$db].[dbo].[Leads] dr WITH(NOLOCK)";

            if (!empty($this->params['subcampaigns'])) {
                $sql .= "
                INNER JOIN #SelectedSubCampaign SC on SC.Subcampaign = dr.Subcampaign";
            }

            $sql .= "
            LEFT JOIN [$db].[dbo].[Dispos] DI ON DI.id = dr.DispositionId
            WHERE dr.GroupId = :group_id$i
            AND dr.Date >= :startdate$i
            AND dr.Date < :enddate$i
            AND dr.Campaign = :campaign$i
            AND CallStatus not in ('CR_CNCT/CON_CAD', 'CR_CNCT/CON_PVD')";

            if (strlen($this->params['attemptsfrom'])) {
                $sql .= " AND DR.Attempt >= " . $this->params['attemptsfrom'];
            }
            if (strlen($this->params['attemptsto'])) {
                $sql .= " AND DR.Attempt <= " . $this->params['attemptsto'];
            }

            $sql .= "
            GROUP BY dr.Subcampaign, dr.CallStatus, DI.IsCallable, dr.WasDialed, DI.Description, DI.Type, dr.Campaign";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp

        INSERT INTO #ShiftReport(Subcampaign, CallStatus, IsCallable, WasDialed, [Description], [Type], Leads)
		SELECT Subcampaign, CallStatus, IsCallable, WasDialed, [Description], [Type], SUM(Leads)
		FROM #LeadCounts
		GROUP BY Subcampaign, CallStatus, IsCallable, WasDialed, [Description], [Type]

        UPDATE #ShiftReport
        SET IsCallable = 1
        WHERE CallStatus in ('[ Not Called ]', 'AGENTSPCB', 'SYS_CALLBACK')";

        if ($this->params['is_callable'] == 'Y') {
            $sql .= "
            DELETE FROM #ShiftReport WHERE IsCallable = 0";
        }
        if ($this->params['is_callable'] == 'N') {
            $sql .= "
            DELETE FROM #ShiftReport WHERE IsCallable = 1";
        }

        $sql .= "
        UPDATE #ShiftReport
        SET TotalLeads = a.Leads
        FROM (SELECT SUM(Leads) as Leads FROM #ShiftReport) a";

        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id1' . $i] = Auth::user()->group_id;
            $bind['campaign1' . $i] = $this->params['campaign'];

            $sql .= "
            UPDATE #ShiftReport
            SET AvailableLeads += a.Leads
                FROM (SELECT COUNT(DISTINCT l.id) as Leads
                        FROM [$db].[dbo].[Leads] l WITH(NOLOCK)
                        LEFT JOIN dialer_DialingSettings ds on ds.GroupId = l.GroupId and ds.Campaign = l.Campaign and ds.Subcampaign = l.Subcampaign
                        LEFT JOIN dialer_DialingSettings ds2 on ds2.GroupId = l.GroupId and ds2.Campaign = l.Campaign";

            if (!empty($this->params['subcampaigns'])) {
                $sql .= "
                INNER JOIN #SelectedSubCampaign SC on SC.Subcampaign = l.Subcampaign";
            }

            $sql .= "
                WHERE l.GroupId = :group_id1$i
                AND l.Campaign = :campaign1$i
                AND (IsNull(ds.MaxDialingAttempts, IsNull(ds2.MaxDialingAttempts, @MaxDialingAttempts)) <> 0
                AND l.Attempt < IsNull(ds.MaxDialingAttempts, IsNull(ds2.MaxDialingAttempts, @MaxDialingAttempts)))
                AND l.WasDialed = 0
            ) a";
        }

        $sql .= "
        UPDATE #ShiftReport
        SET [Description] = CallStatus
        WHERE IsNull([Description], '') = ''

        SELECT
            Subcampaign,
            [Description],
            [Type],
            SUM(Leads) as Leads,
            TotalLeads,
            AvailableLeads,
            totRows = COUNT(*) OVER()
        FROM #ShiftReport
        GROUP BY Subcampaign, [Description], [Type], TotalLeads, AvailableLeads, IsCallable
        ORDER BY Subcampaign, [Description]";

        return [$sql, $bind];
    }

    private function processResults($sql, $bind)
    {
        $results = [];
        $this->extras['TotalLeads'] = 0;
        $this->extras['AvailableLeads'] = 0;

        $subtotal_rec = [
            'Subcampaign' => null,
            'Description' => '',
            'Type' => '',
            'Leads' => 0,
            'isSubtotal' => 1,
        ];

        foreach ($this->yieldSql($sql, $bind) as $rec) {
            if (empty($rec)) {
                continue;
            }

            $this->params['totrows'] = $rec['totRows'];
            $this->extras['TotalLeads'] = $rec['TotalLeads'];
            $this->extras['AvailableLeads'] = $rec['AvailableLeads'];

            unset($rec['totRows']);
            unset($rec['TotalLeads']);
            unset($rec['AvailableLeads']);

            if ($subtotal_rec['Subcampaign'] === null) {
                $subtotal_rec['Subcampaign'] = $rec['Subcampaign'];
            }

            if ($subtotal_rec['Subcampaign'] != $rec['Subcampaign']) {
                $subtotal_rec['Subcampaign'] .= ' ' . trans('reports.subtotal');
                $results[] = $subtotal_rec;
                $subtotal_rec['Subcampaign'] = $rec['Subcampaign'];
                $subtotal_rec['Leads'] = 0;
            }

            $subtotal_rec['Leads'] += $rec['Leads'];

            $results[] = $rec;
        }

        if ($subtotal_rec['Subcampaign'] !== null) {
            $subtotal_rec['Subcampaign'] .= ' ' . trans('reports.subtotal');
            $results[] = $subtotal_rec;
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

        if (!empty($request->campaign)) {
            $this->params['campaign'] = $request->campaign;
        } else {
            $this->errors->add('campaign.required', trans('reports.errcampaignrequired'));
        }

        if (!empty($request->subcampaigns)) {
            $this->params['subcampaigns'] = $request->subcampaigns;
        }

        if (!empty($request->attemptsfrom) || $request->attemptsfrom == 0) {
            $this->params['attemptsfrom'] = $request->attemptsfrom;
        }

        if (
            !empty($request->attemptsto) || $request->attemptsto == 0
        ) {
            $this->params['attemptsto'] = $request->attemptsto;
        }

        if (!empty($request->is_callable)) {
            $this->params['is_callable'] = $request->is_callable;
        }

        // Save params to session
        $this->saveSessionParams();

        return $this->errors;
    }
}
