<?php

namespace App\Services\Reports;

use App\Models\Dialer;
use App\Traits\CampaignTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\ReportTraits;
use Illuminate\Support\Carbon;

class CallDetails
{
    use ReportTraits;
    use CampaignTraits;

    private $advanced_table;
    private $extra_cols;

    public function __construct()
    {
        $this->initilaizeParams();

        $this->params['reportName'] = 'reports.call_details';
        $this->params['fromdate'] = '';
        $this->params['todate'] = '';
        $this->params['campaigns'] = [];
        $this->params['subcampaigns'] = [];
        $this->params['custom_table'] = '';
        $this->params['reps'] = [];
        $this->params['skills'] = [];
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
            'Date' => 'reports.date',
            'Rep' => 'reports.rep',
            'Campaign' => 'reports.campaign',
            'Subcampaign' => 'reports.subcampaign',
            'Phone' => 'reports.phone',
            'Attempt' => 'reports.attempt',
            'CallerId' => 'reports.callerid',
            'InboundSource' => 'reports.source',
            'LastName' => 'reports.lastname',
            'FirstName' => 'reports.firstname',
            'ImportDate' => 'reports.import_date',
            'CallStatus' => 'reports.callstatus',
            'IsCallable' => 'reports.is_callable',
            'Duration' => 'reports.duration',
            'CallType' => 'reports.calltype',
            'Details' => 'reports.details',
            'AgentHangup' => 'reports.agent_hangup',
        ];
    }

    public function getFilters()
    {
        $filters = [
            'campaigns' => $this->getAllCampaigns(),
            'subcampaigns' => [],
            'custom_table' => $this->getAllCustomTables(),
            'inbound_sources' => $this->getAllInboundSources(),
            'reps' => $this->getAllReps(true),
            'skills' => $this->getAllSkills(),
            'call_statuses' => $this->getAllCallStatuses(),
            'call_types' => $this->getAllCallTypes(),
            'is_callable' => [
                '' => '',
                'Y' => trans('general.yes'),
                'N' => trans('general.no'),
            ],
            'db_list' => Auth::user()->getDatabaseArray(),
            'showonlyterm' => 1,
        ];

        // Remove SMS and add 'all' to list of call types
        unset($filters['call_types']['TextMessage']);
        $filters['call_types'] = array_merge(['' => 'All'], $filters['call_types']);

        return $filters;
    }

    public function getInfo()
    {
        return [
            'columns' => $this->params['columns'],
            'paragraphs' => 2,
        ];
    }

    private function getAllCustomTables()
    {
        $sql = "SELECT TableName
        FROM AdvancedTables
        WHERE GroupId = :group_id
        ORDER BY TableName";

        $results = $this->runSql($sql, ['group_id' => Auth::user()->group_id]);

        return resultsToList($results);
    }

    private function getExtraCols($table)
    {
        $sql = "SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_NAME = '$table'
            and COLUMN_NAME != 'LeadId'
            ORDER BY ORDINAL_POSITION";

        $results = $this->runSql($sql, []);

        return array_values(resultsToList($results));
    }

    private function configCustomTable()
    {
        $this->advanced_table = '';
        $this->extra_cols = '';

        if (!empty($this->params['custom_table'])) {
            $this->advanced_table = 'ADVANCED_' . $this->params['custom_table'];
            $extra_col_array = $this->getExtraCols($this->advanced_table);

            foreach ($extra_col_array as $col) {
                $this->params['columns'][$col] = $col;
                $this->extra_cols .= ', A.[' . $col . ']';
            }
        }
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

    public function processRow($rec)
    {
        // remove tot count
        array_pop($rec);

        $rec['Date'] = Carbon::parse($rec['Date'])->isoFormat('L LT');

        $rec['Duration'] = $this->secondsToHms($rec['Duration']);

        if (!empty($rec['Recording'])) {
            if ($rec['Duration'] > 0 && !($rec['CallType'] == 'Inbound' && $rec['CallStatus'] == 'CR_HANGUP')) {
                $server = Dialer::where('reporting_db', Auth::user()->db)->first()->dialer_fqdn;
                $file_id = str_replace('-', '', $rec['Recording']);

                $rec['Recording'] = '
                <audio controls preload="none"
                src="https://' . $server . '/Agent/Recordings.aspx?id=' . $file_id . '" type="audio/wav">
                Your browser does not support the audio tag.
            	</audio>';
            } else {
                $rec['Recording'] = '';
            }
        }

        if (!empty($rec['ImportDate'])) {
            $rec['ImportDate'] = Carbon::parse($rec['ImportDate'])->isoFormat('L LT');
        }

        return $rec;
    }

    public function makeQuery($all)
    {
        $this->setHeadings();

        list($fromDate, $toDate) = $this->dateRange($this->params['fromdate'], $this->params['todate']);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $tz = Auth::user()->tz;

        $answered = 0;
        $unanswered = 0;

        $bind['group_id'] =  Auth::user()->group_id;
        $bind['startdate'] = $startDate;
        $bind['enddate'] = $endDate;
        $bind['group_id_aa'] =  Auth::user()->group_id;
        $bind['startdate_aa'] = $startDate;
        $bind['enddate_aa'] = $endDate;

        // create temp tables for joins
        $sql = "SET NOCOUNT ON;
        CREATE TABLE #SelectedCampaign(CampaignName varchar(50) Primary Key);
        CREATE TABLE #SelectedSubcampaign(SubcampaignName varchar(50) Primary Key);
        CREATE TABLE #SelectedRep(RepName varchar(50) Primary Key);
        CREATE TABLE #SelectedCallStatus(CallStatusName varchar(50) Primary Key);
        CREATE TABLE #SelectedSource(SourceName varchar(50) Primary Key);";

        if (!empty($this->params['skills'])) {
            $list = str_replace("'", "''", implode('!#!', $this->params['skills']));
            $sql .= "
            CREATE TABLE #SelectedSkill(SkillName varchar(50) Primary Key);
            INSERT INTO #SelectedSkill SELECT DISTINCT [value] from dbo.SPLIT('$list', '!#!');";
        }

        $where = '';
        // load temp tables

        // Load all hangup AA recs +/- 1 hour of date range
        $sql .= "
        SELECT AA.ActivityId, AA.Rep
        INTO #tmphangups
        FROM AgentActivity AA WITH(NOLOCK)";

        if (!empty($this->params['skills'])) {
            $sql .= "
                INNER JOIN Reps RR on RR.RepName COLLATE SQL_Latin1_General_CP1_CS_AS = AA.Rep
                INNER JOIN #SelectedSkill SS on SS.SkillName COLLATE SQL_Latin1_General_CP1_CS_AS = RR.Skill";
        }

        $sql .= "
        WHERE  AA.GroupId = :group_id_aa
        AND AA.Details = 'Agent Hangup Call'
        AND AA.Date >= DATEADD(hour,-1,:startdate_aa)
        AND AA.Date <= DATEADD(hour,1,:enddate_aa)

        CREATE INDEX IX_tmphangup ON #tmphangups (ActivityId, Rep);
        ";

        if (!empty($this->params['campaigns']) && $this->params['campaigns'] != '*') {
            $campaigns = str_replace("'", "''", implode('!#!', $this->params['campaigns']));
            $bind['campaigns'] = $campaigns;

            $where .= " AND C.CampaignName IS NOT NULL";
            $sql .= "
            INSERT INTO #SelectedCampaign SELECT DISTINCT [value] from dbo.SPLIT(:campaigns, '!#!');";
        }

        if (!empty($this->params['subcampaigns'])) {
            $subcampaigns = str_replace("'", "''", implode('!#!', $this->params['subcampaigns']));
            $bind['subcampaigns'] = $subcampaigns;

            $where .= " AND SC.SubcampaignName IS NOT NULL";
            $sql .= "
            INSERT INTO #SelectedSubcampaign SELECT DISTINCT [value] from dbo.SPLIT(:subcampaigns, '!#!');";
        }


        if (!empty($this->params['reps']) && $this->params['reps'] != '*') {

            if (array_search('[All Answered]', $this->params['reps']) !== false) {
                $answered = 1;
            }
            if (array_search('[All Unanswered]', $this->params['reps']) !== false) {
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
        if (strlen($this->params['durationfrom'])) {
            $where .= " AND DR.Duration >= " . $this->params['durationfrom'];
        }
        if (strlen($this->params['durationto'])) {
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

        $is_callable_sql = "IsNull((SELECT TOP 1 D.IsCallable
                FROM [Dispos] D
                WHERE D.Disposition = DR.CallStatus
                AND (GroupId = DR.GroupId OR IsSystem=1)
                AND (Campaign = DR.Campaign OR Campaign = '')
                ORDER BY [id] Desc
                ), 0)";

        $sql .= " SELECT *, totRows = COUNT(*) OVER()
                FROM (SELECT
                CONVERT(datetimeoffset, DR.Date) AT TIME ZONE '$tz' as Date,
                IsNull(DR.Rep, '') as Rep,
                DR.Campaign,
                DR.Subcampaign,
                DR.Phone,
                DR.Attempt,
                DR.CallerId,
                SRC.Description,
                L.LastName,
                L.FirstName,
                CONVERT(datetimeoffset, L.Date) AT TIME ZONE '$tz' as ImportDate,
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
                DR.Details,
                AA.Details as AgentHangup";

        // add extra cols for superadmins
        if (Auth::user()->istype('superadmin') || session('isSsoAdmin', 0) || session('isSsoSuperadmin', 0)) {
            $this->params['columns'] += ['Route' => 'Route'];
            $sql .= ",DR.Route";
        }

        // add extra cols for admins
        if (Auth::user()->isType(['admin', 'superadmin']) || session('isSsoAdmin', 0)) {
            // Add audio player column if not doing an export
            if (!$this->export) {
                $this->params['columns'] += ['Recording' => 'Recording'];
                $sql .= ",DR.RecordId as Recording";
            }
        }

        $sql .= "
                $this->extra_cols
            FROM [DialingResults] DR WITH(NOLOCK)
            LEFT JOIN InboundSources SRC on SRC.GroupId = DR.GroupId AND SRC.InboundSource = DR.CallerId AND DR.CallType = 1";

        if (!empty($this->params['skills'])) {
            $sql .= "
                INNER JOIN Reps RR on RR.RepName COLLATE SQL_Latin1_General_CP1_CS_AS = DR.Rep
                INNER JOIN #SelectedSkill SS on SS.SkillName COLLATE SQL_Latin1_General_CP1_CS_AS = RR.Skill";
        }

        $sql .= "
            OUTER APPLY (SELECT TOP 1 'Agent Hangup' as Details
                FROM #tmphangups AA
                WHERE AA.ActivityId = DR.ActivityId
                AND AA.Rep = DR.Rep
                ) AA
            LEFT OUTER JOIN [Leads] L ON L.id = DR.LeadId";

        if (!(empty($this->advanced_table))) {
            $sql .= "
                LEFT OUTER JOIN [$this->advanced_table] A ON A.LeadId = L.IdGuid";
        }

        $sql .= "
            LEFT JOIN #SelectedCampaign C on C.CampaignName = DR.Campaign
            LEFT JOIN #SelectedSubcampaign SC on SC.SubcampaignName = DR.Subcampaign
            LEFT JOIN #SelectedRep R on R.RepName COLLATE SQL_Latin1_General_CP1_CS_AS = DR.Rep
            LEFT JOIN #SelectedCallStatus CS on CS.CallStatusName = DR.CallStatus
            LEFT JOIN #SelectedSource S on S.SourceName = DR.CallerId
            WHERE DR.GroupId = :group_id
            AND dr.Date >= :startdate
            AND DR.Date <= :enddate
            AND DR.CallType != 7
            $where";

        // sql server goofyness
        if (!empty($this->params['is_callable'])) {
            $bind['is_callable'] = $this->params['is_callable'] == 'Y' ? 1 : 0;
            $sql .= " AND $is_callable_sql = :is_callable";
        }

        if (session('ssoRelativeCampaigns', 0)) {
            $sql .= " AND DR.Campaign IN (SELECT CampaignName FROM dbo.GetAllRelativeCampaigns(:ssousercamp, 1))";
            $bind['ssousercamp'] = session('ssoUsername');
        }

        if (session('ssoRelativeReps', 0)) {
            $sql .= " AND DR.Rep IN (SELECT RepName FROM dbo.GetAllRelativeReps(:ssouserrep))";
            $bind['ssouserrep'] = session('ssoUsername');
        }

        $sql .= ") tmptable";

        if (strlen($this->params['calltype']) !== 0) {
            $sql .= " WHERE CallType = '" . $this->params['calltype'] . "'";
        }

        // Check params
        if (!empty($this->params['orderby']) && is_array($this->params['orderby'])) {
            $sort = '';
            foreach ($this->params['orderby'] as $col => $dir) {
                $sort .= ",[$col] $dir";
            }
            $sql .= ' ORDER BY ' . substr($sort, 1);
        } else {
            $sql .= ' ORDER BY Date';
        }

        if (!$all) {
            $offset = ($this->params['curpage'] - 1) * $this->params['pagesize'];
            $sql .= " OFFSET $offset ROWS FETCH NEXT " . $this->params['pagesize'] . " ROWS ONLY";
        }

        return [$sql, $bind];
    }

    private function processInput(Request $request)
    {
        // Get vals from session if not set (for exports)
        $request = $this->getSessionParams($request);

        // Get custom table first, so we can set cols, so sorting will work in checkPageFilters()
        if (!empty($request->custom_table)) {
            $this->params['custom_table'] = $request->custom_table;
        }
        $this->configCustomTable();

        // Check page filters
        $this->checkPageFilters($request);

        // Check report filters
        $this->checkDateRangeFilters($request);

        if (empty($request->campaigns)) {
            $this->errors->add('campaign.required', trans('reports.errcampaignrequired'));
        } else {
            $this->params['campaigns'] = $request->campaigns;
        }

        if (!empty($request->subcampaigns)) {
            $this->params['subcampaigns'] = $request->subcampaigns;
        }

        if (!empty($request->reps)) {
            $this->params['reps'] = $request->reps;
        }

        if (!empty($request->skills)) {
            $this->params['skills'] = $request->skills;
        }

        if (!empty($request->is_callable)) {
            $this->params['is_callable'] = $request->is_callable;
        }

        if (!empty($request->call_type)) {
            $this->params['calltype'] = $request->call_type;
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

        if (!empty($request->durationfrom) || $request->durationfrom == 0) {
            $this->params['durationfrom'] = $request->durationfrom;
        }

        if (!empty($request->durationto) || $request->durationto == 0) {
            $this->params['durationto'] = $request->durationto;
        }

        // If one is filled but not the other, then fake it
        if (strlen($this->params['durationfrom']) && !strlen($this->params['durationto'])) {
            $this->params['durationto'] = 999999;
        }
        if (strlen($this->params['durationto']) && !strlen($this->params['durationfrom'])) {
            $this->params['durationfrom'] = -999;
        }

        if ($this->params['durationfrom'] > $this->params['durationto']) {
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
