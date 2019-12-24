<?php

namespace App\Services\Reports;

use App\Traits\CampaignTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\ReportTraits;
use Illuminate\Support\Carbon;

class CallDetails
{
    use ReportTraits;
    use CampaignTraits;

    public function __construct()
    {
        $this->initilaizeParams();

        $this->params['reportName'] = 'reports.call_details';
        $this->params['fromdate'] = '';
        $this->params['todate'] = '';
        $this->params['campaigns'] = [];
        $this->params['reps'] = [];
        $this->params['is_callable'] = '';
        $this->params['calltype'] = '';
        $this->params['phone'] = '';
        $this->params['callerids'] = [];
        $this->params['callerid'] = '';
        $this->params['call_statuses'] = [];
        $this->params['durationfrom'] = '';
        $this->params['durationto'] = '';
        $this->params['showonlyterm'] = 0;
        $this->params['columns'] = [
            'Rep' => 'reports.rep',
            'Campaign' => 'reports.campaign',
            'Phone' => 'reports.phone',
            'LastName' => 'reports.lastname',
            'FirstName' => 'reports.firstname',
            'Date' => 'reports.date',
            'CallStatus' => 'reports.callstatus',
            'IsCallable' => 'reports.is_callable',
            'Duration' => 'reports.duration',
            'CallType' => 'reports.calltype',
            'Details' => 'reports.details',
        ];
    }

    public function getFilters()
    {
        $filters = [
            'campaigns' => $this->getAllCampaigns(
                $this->params['fromdate'],
                $this->params['todate']
            ),
            'inbound_sources' => $this->getAllInboundSources(),
            'reps' => $this->getAllReps(true),
            'call_statuses' => $this->getAllCallStatuses(),
            'call_types' => $this->getAllCallTypes(),
            'is_callable' => [
                '' => '',
                'Y' => trans('general.yes'),
                'N' => trans('general.no'),
            ],
            'db_list' => Auth::user()->getDatabaseArray(),
        ];

        // Remove SMS and add 'all' to list of call types
        unset($filters['call_types']['TextMessage']);
        $filters['call_types'] = array_merge(['' => 'All'], $filters['call_types']);

        return $filters;
    }

    private function executeReport($all = false)
    {
        $this->setHeadings();

        list($fromDate, $toDate) = $this->dateRange($this->params['fromdate'], $this->params['todate']);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $tz = Auth::user()->tz;

        $answered = 0;
        $unanswered = 0;
        $answered = 0;
        $unanswered = 0;

        // create temp tables for joins
        $sql = "SET NOCOUNT ON;
        CREATE TABLE #SelectedCampaign(CampaignName varchar(50) Primary Key);
        CREATE TABLE #SelectedRep(RepName varchar(50) Primary Key);
        CREATE TABLE #SelectedCallStatus(CallStatusName varchar(50) Primary Key);
        CREATE TABLE #SelectedSource(SourceName varchar(50) Primary Key);";

        $where = '';
        // load temp tables
        if (!empty($this->params['campaigns']) && $this->params['campaigns'] != '*') {
            $campaigns = str_replace("'", "''", implode('!#!', $this->params['campaigns']));
            $bind['campaigns'] = $campaigns;

            $where .= " AND C.CampaignName IS NOT NULL";
            $sql .= "
            INSERT INTO #SelectedCampaign SELECT DISTINCT [value] from dbo.SPLIT(:campaigns, '!#!');";
        }

        if (!empty($this->params['reps']) && $this->params['reps'] != '*') {

            if (isset($this->params['reps']['[ All Answered'])) {
                $answered = 1;
            }
            if (isset($this->params['reps']['[ All Unanswered'])) {
                $unanswered = 1;
            }
            if ($answered && $unanswered) {
                $answered = 0;
                $unanswered = 0;
            }

            $reps = str_replace("'", "''", implode('!#!', $this->params['reps']));
            $bind['reps'] = $reps;

            $where .= " AND R.RepName IS NOT NULL";
            $sql .= "
            INSERT INTO #SelectedRep SELECT DISTINCT [value] from dbo.SPLIT(:reps, '!#!');";
        }
        if (!empty($this->params['call_statuses']) && $this->params['call_statuses'] != '*') {
            $call_statuses = str_replace("'", "''", implode('!#!', $this->params['call_statuses']));
            $bind['call_statuses'] = $call_statuses;

            $where .= " AND CS.CallStatusName IS NOT NULL";
            $sql .= "
            INSERT INTO #SelectedCallStatus SELECT DISTINCT [value] from dbo.SPLIT(:call_statuses, '!#!');";
        }
        if (!empty($this->params['callerids']) && $this->params['callerids'] != '*') {
            $callerids = str_replace("'", "''", implode('!#!', $this->params['callerids']));
            $bind['callerids'] = $callerids;

            $where .= " AND S.SourceName IS NOT NULL";
            $sql .= "
            INSERT INTO #SelectedSource SELECT DISTINCT [value] from dbo.SPLIT(:callerids, '!#!');";
        }
        if (!empty($this->params['callerid']) && $this->params['callerid'] != '*') {
            $bind['callerid'] = $this->params['callerid'];
            $where .= " AND DR.CallerId = :callerid";
        }
        if (!empty($this->params['durationfrom'])) {
            $where .= " AND DR.Duration >= " . $this->params['durationfrom'];
        }
        if (!empty($this->params['durationto'])) {
            $where .= " AND DR.Duration <= " . $this->params['durationto'];
        }
        if (!empty($this->params['showonlyterm'])) {
            $where .= " AND DR.CallStatus NOT IN ('Inbound', 'CR_CNCT/CON_CAD', 'CR_CNCT/CON_PVD')";
        }
        if (!empty($this->params['phone']) && $this->params['phone'] != '*') {
            $bind['phone'] = $this->params['phone'];
            $where .= " AND DR.Phone LIKE '1' + :phone + '%'";
        }
        if ($unanswered) {
            $where .= " AND DR.LeadSessionId IS NULL OR (IsNull(DR.Rep, '') = '' AND IsNull(DR.CallStatus, '') = '')";
        }

        // for inbound only?
        if ($answered) {
            $where .= " AND (IsNull(DR.CallStatus, '') NOT IN ('Inbound', 'Inbound Voicemail') AND IsNull(DR.Rep, '') <> '')";
        }

        $sql .= " SELECT * INTO #BigTable FROM (";

        $union = '';
        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id' . $i] =  Auth::user()->group_id;
            $bind['startdate' . $i] = $startDate;
            $bind['enddate' . $i] = $endDate;

            $is_callable_sql = "IsNull((SELECT TOP 1 D.IsCallable
                FROM [$db].[dbo].[Dispos] D
                WHERE D.Disposition = DR.CallStatus
                AND (GroupId = DR.GroupId OR IsSystem=1)
                AND (Campaign = DR.Campaign OR Campaign = '')
                ORDER BY [Description] Desc
                ), 0)";

            $sql .= " $union SELECT
                IsNull(DR.Rep, '') as Rep,
                DR.Campaign,
                DR.Phone,
                L.LastName,
                L.FirstName,
                CONVERT(datetimeoffset, DR.Date) AT TIME ZONE '$tz' as Date,
                CASE DR.LeadId
                    WHEN -1 THEN '_MANUAL_CALL_'
                    ELSE IsNull(DR.CallStatus, '')
                END as CallStatus,
                $is_callable_sql as IsCallable,
                DR.Duration,
                CASE
                    WHEN DR.CallType= -1 THEN ''
                    WHEN DR.CallType = 0 THEN 'Outbound'
                    WHEN DR.CallType = 1 THEN 'Inbound'
                    WHEN DR.CallType = 2 THEN 'Manual'
                    WHEN DR.CallType = 4 THEN 'Conference'
                    WHEN DR.CallType = 5 THEN 'Progressive'
                    WHEN DR.CallType = 6 THEN 'Transferred'
                    WHEN DR.CallType >= 10 THEN 'Transferred'
                    ELSE 'Unknown'
                END as CallType,
                DR.Details
            FROM [$db].[dbo].[DialingResults] DR WITH(NOLOCK)
            LEFT OUTER JOIN [$db].[dbo].[Leads] L ON L.id = DR.LeadId
            LEFT JOIN #SelectedCampaign C on C.CampaignName = DR.Campaign
            LEFT JOIN #SelectedRep R on R.RepName COLLATE SQL_Latin1_General_CP1_CS_AS = DR.Rep
            LEFT JOIN #SelectedCallStatus CS on CS.CallStatusName = DR.CallStatus
            LEFT JOIN #SelectedSource S on S.SourceName = DR.CallerId
            WHERE DR.GroupId = :group_id$i
            AND dr.Date >= :startdate$i
            AND DR.Date <= :enddate$i
            AND DR.CallType != 7
            $where";

            // sql server goofyness
            if (!empty($this->params['is_callable'])) {
                $bind['is_callable' . $i] = $this->params['is_callable'] == 'Y' ? 1 : 0;
                $sql .= " AND $is_callable_sql = :is_callable$i";
            }

            $union = 'UNION ALL';
        }

        $sql .= ") tmp;
        SELECT *, totRows = COUNT(*) OVER()
        FROM #BigTable";

        if (!empty($this->params['calltype'])) {
            $sql .= " WHERE CallType = '" . $this->params['calltype'] . "'";
        }

        // Check params
        if (!empty($this->params['orderby']) && is_array($this->params['orderby'])) {
            $sort = '';
            foreach ($this->params['orderby'] as $col => $dir) {
                $sort .= ",$col $dir";
            }
            $sql .= ' ORDER BY ' . substr($sort, 1);
        } else {
            $sql .= ' ORDER BY Date';
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
        } else {
            $this->params['totrows'] = $results[0]['totRows'];

            foreach ($results as &$rec) {
                array_pop($rec);
                $rec['Date'] = Carbon::parse($rec['Date'])->isoFormat('L LT');
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

        if (empty($request->campaigns)) {
            $this->errors->add('campaign.required', trans('reports.errcampaignrequired'));
        } else {
            $this->params['campaigns'] = $request->campaigns;
        }

        if (!empty($request->reps)) {
            $this->params['reps'] = $request->reps;
        }

        if (!empty($request->is_callable)) {
            $this->params['is_callable'] = $request->is_callable;
        }

        if (!empty($request->calltype)) {
            $this->params['calltype'] = $request->calltype;
        }

        if (!empty($request->phone)) {
            $this->params['phone'] = $request->phone;
        }

        if (!empty($request->callerids)) {
            $this->params['callerids'] = $request->callerids;
        }

        if (!empty($request->callerid)) {
            $this->params['callerid'] = $request->callerid;
        }

        if (!empty($request->call_statuses)) {
            $this->params['call_statuses'] = $request->call_statuses;
        }

        if (empty($request->durationfrom)) {
            $this->params['durationfrom'] = '';
            $from = 0;
        } else {
            $this->params['durationfrom'] = $request->durationfrom;
            $from = $request->durationfrom;
        }

        if (empty($request->durationto)) {
            $this->params['durationto'] = '';
            $to = 0;
        } else {
            $this->params['durationto'] = $request->durationto;
            $to = $request->durationto;
        }

        if ($from > $to) {
            $this->errors->add('duration', trans('reports.errduration'));
        }

        if (!empty($request->showonlyterm)) {
            $this->params['showonlyterm'] = $request->showonlyterm;
        }

        // Save params to session
        $this->saveSessionParams();

        return $this->errors;
    }
}
