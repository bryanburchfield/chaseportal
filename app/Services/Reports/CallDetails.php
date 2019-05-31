<?php

namespace App\Services\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class CallDetails
{
    use ReportTraits;

    public function __construct()
    {
        $this->initilaizeParams();

        $this->params['fromdate'] = '';
        $this->params['todate'] = '';
        $this->params['campaigns'] = [];
        $this->params['reps'] = [];
        $this->params['calltype'] = '';
        $this->params['phone'] = '';
        $this->params['callerids'] = [];
        $this->params['callstatuses'] = [];
        $this->params['durationfrom'] = '';
        $this->params['durationto'] = '';
        $this->params['showonlyterm'] = 0;
        $this->params['columns'] = [
            'Rep' => 'Rep',
            'Campaign' => 'Campaign',
            'Phone' => 'Phone',
            'Date' => 'Date',
            'CallStatus' => 'Call Status',
            'Duration' => 'Duration',
            'CallType' => 'Call Type',
            'Details' => 'Call Details',
        ];
    }

    public function getFilters()
    {
        $filters = [
            'campaigns' => $this->getAllCampaigns(),
            'inbound_sources' => $this->getAllInboundSources(),
            'reps' => $this->getAllReps(true),
            'call_statuses' => $this->getAllCallStatuses(),
            'call_types' => $this->getAllCallTypes(),
        ];

        // Add 'all' to list of call types
        $filters['call_types'] = array_merge(['' => 'All'], $filters['call_types']);

        return $filters;
    }

    private function executeReport($all = false)
    {
        // Log::debug($this->params);
        list($fromDate, $toDate) = $this->dateRange($this->params['fromdate'], $this->params['todate']);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $bind['group_id'] =  Auth::user()->group_id;
        $bind['tz'] = Auth::user()->tz;
        $bind['startdate'] = $startDate;
        $bind['enddate'] = $endDate;

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
        if (!empty($this->params['campaign']) && $this->params['campaign'] != '*') {
            $where .= " AND C.CampaignName IS NOT NULL";
            $list = str_replace("'", "''", implode('!#!', $this->params['campaign']));
            $sql .= "
            INSERT INTO #SelectedCampaign SELECT DISTINCT [value] from dbo.SPLIT('$list', '!#!');";
        }

        if (!empty($this->params['rep']) && $this->params['rep'] != '*') {

            if (isset($this->params['rep']['[ All Answered'])) {
                $answered = 1;
            }
            if (isset($this->params['rep']['[ All Unanswered'])) {
                $unanswered = 1;
            }
            if ($answered && $unanswered) {
                $answered = 0;
                $unanswered = 0;
            }

            $where .= " AND R.RepName IS NOT NULL";
            $list = str_replace("'", "''", implode('!#!', $this->params['rep']));
            $sql .= "
            INSERT INTO #SelectedRep SELECT DISTINCT [value] from dbo.SPLIT('$list', '!#!');";
        }
        if (!empty($this->params['callstatus']) && $this->params['callstatus'] != '*') {
            $where .= " AND CS.CallStatusName IS NOT NULL";
            $list = str_replace("'", "''", implode('!#!', $this->params['callstatus']));
            $sql .= "
            INSERT INTO #SelectedCallStatus SELECT DISTINCT [value] from dbo.SPLIT('$list', '!#!');";
        }
        if (!empty($this->params['callerid']) && $this->params['callerid'] != '*') {
            $where .= " AND S.SourceName IS NOT NULL";
            $list = str_replace("'", "''", implode('!#!', $this->params['callerid']));
            $sql .= "
            INSERT INTO #SelectedSource SELECT DISTINCT [value] from dbo.SPLIT('$list', '!#!');";
        }
        if (!empty($this->params['durationfrom'])) {
            $where .= " AND DR.Duration >= :durationfrom";
            $bind['durationfrom'] = $this->params['durationfrom'];
        }
        if (!empty($this->params['durationto'])) {
            $where .= " AND DR.Duration <= :durationto";
            $bind['durationto'] = $this->params['durationto'];
        }
        if (!empty($this->params['showonlyterm'])) {
            $where .= " AND DR.CallStatus NOT IN ('Inbound', 'CR_CNCT/CON_CAD', 'CR_CNCT/CON_PVD')";
        }
        if (!empty($this->params['phone']) && $this->params['phone'] != '*') {
            $where .= " AND DR.Phone LIKE '1' + :phone + '%')";
            $bind['phone'] = $this->params['phone'];
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
        foreach (Auth::user()->getDatabaseArray() as $db) {
            $sql .= " $union SELECT 
                IsNull(DR.Rep, '') as Rep,
                DR.Campaign,
                DR.Phone, 
                CONVERT(datetimeoffset, DR.Date) AT TIME ZONE :tz as Date,
                CASE DR.LeadId
                    WHEN -1 THEN '_MANUAL_CALL_'
                    ELSE IsNull(DR.CallStatus, '')
                END as CallStatus,
                DR.Duration,
                CASE 
                    WHEN DR.CallType= -1 THEN ''
                    WHEN DR.CallType = 0 THEN 'Outbound'
                    WHEN DR.CallType = 1 THEN 'Inbound'
                    WHEN DR.CallType = 2 THEN 'Manual'
                    WHEN DR.CallType = 4 THEN 'Conference'
                    WHEN DR.CallType = 5 THEN 'Progressive'
                    WHEN DR.CallType = 6 THEN 'Transferred'
                    WHEN DR.CallType = 7 THEN 'TextMessage'
                    WHEN DR.CallType >= 10 THEN 'Transferred'
                    ELSE 'Unknown'
                END as CallType,
                DR.Details
            FROM [$db].[dbo].[DialingResults] DR WITH(NOLOCK)
            LEFT JOIN #SelectedCampaign C on C.CampaignName = DR.Campaign
            LEFT JOIN #SelectedRep R on R.RepName COLLATE SQL_Latin1_General_CP1_CS_AS = DR.Rep
            LEFT JOIN #SelectedCallStatus CS on CS.CallStatusName = DR.CallStatus
            LEFT JOIN #SelectedSource S on S.SourceName = DR.CallerId
            WHERE DR.GroupId = :group_id
            AND dr.Date >= :startdate
            AND DR.Date <= :enddate
            $where";

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

        // Log::debug($sql);
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
                $rec['Date'] = (new \DateTime($rec['Date']))->format('m/d/Y h:i:s A');
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

        if (empty($request->campaigns)) {
            $this->errors->add('campaign.required', "Campaign required");
        } else {
            $this->params['campaigns'] = $request->campaigns;
        }

        if (!empty($request->reps)) {
            $this->params['reps'] = $request->reps;
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

        if (!empty($request->callstatuses)) {
            $this->params['callstatuses'] = $request->callstatuses;
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
            $this->errors->add('duration', "Invalid Duration values");
        }

        if (!empty($request->showonlyterm)) {
            $this->params['showonlyterm'] = $request->showonlyterm;
        }

        return $this->errors;
    }
}
