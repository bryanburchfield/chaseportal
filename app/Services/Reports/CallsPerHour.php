<?php

namespace App\Services\Reports;

use App\Traits\CampaignTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\ReportTraits;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CallsPerHour
{
    use ReportTraits;
    use CampaignTraits;

    public function __construct()
    {
        $this->initilaizeParams();

        $this->params['reportName'] = 'reports.calls_per_hour';
        $this->params['nostreaming'] = true;
        $this->params['campaigns'] = [];
        $this->params['reps'] = [];
        $this->params['call_type'] = '';
        $this->params['columns'] = [
            'Date' => 'reports.hour',
            'TotalCalls' => 'reports.totalcalls',
            'Connects' => 'reports.connects',
            'Contacts' => 'reports.contacts',
            'Sales' => 'reports.sales',
            'ConversionRate' => 'reports.conversionrate',
            'Inbound' => 'reports.inbound',
            'InboundPct' => 'reports.inbound_pct',
            'Abandoned' => 'reports.abandoned',
            'AbandonRate' => 'reports.abandonrate',
            'Outbound' => 'reports.outbound',
            'OutboundPct' => 'reports.outbound_pct',
            'Dropped' => 'reports.dropped',
            'DroppedRate' => 'reports.droprate',
            'TalkTime' => 'reports.talktimesec',
            'ContactRatio' => 'reports.contactpct',
        ];
    }

    public function getFilters()
    {
        $filters = [
            'db_list' => Auth::user()->getDatabaseArray(),
            'campaigns' => $this->getAllCampaigns(
                $this->params['fromdate'],
                $this->params['todate']
            ),
            'reps' => $this->getAllReps(),
            'call_types' =>  [
                '' => 'All',
                'Inbound' => 'Inbound',
                'Outbound' => 'Outbound',
            ]
        ];

        return $filters;
    }

    private function executeReport($all = false)
    {
        list($sql, $bind) = $this->makeQuery($all);

        $results = $this->processResults($sql, $bind);

        if (empty($results)) {
            $this->params['totrows'] = 0;
            $this->params['totpages'] = 1;
            $this->params['curpage'] = 1;
        } else {
            $this->params['totrows'] = count($results);
            $this->params['totpages'] = floor($this->params['totrows'] / $this->params['pagesize']);
            $this->params['totpages'] += floor($this->params['totrows'] / $this->params['pagesize']) == ($this->params['totrows'] / $this->params['pagesize']) ? 0 : 1;
        }

        return $this->getPage($results);
    }

    public function makeQuery($all)
    {
        $this->setHeadings();

        list($fromDate, $toDate) = $this->dateRange($this->params['fromdate'], $this->params['todate']);

        $timeZoneName = Auth::user()->tz;

        $xAxis = "DATEADD(HOUR, DATEPART(HOUR, CONVERT(datetimeoffset, Date) AT TIME ZONE '$timeZoneName'),
            CAST(CAST(CONVERT(datetimeoffset, Date) AT TIME ZONE '$timeZoneName' AS DATE) AS DATETIME))";

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $bind = [
            'groupid' =>  Auth::user()->group_id,
            'startdate' => $startDate,
            'enddate' => $endDate,
        ];

        // filter on call type
        switch ($this->params['call_type']) {
            case 'Inbound':
                $call_type_where = 'DR.CallType IN (1,11)';
                break;
            case 'Outbound':
                $call_type_where = 'DR.CallType NOT IN (1,7,8,11)';
                break;
            default:
                $call_type_where = 'DR.CallType NOT IN (7,8)';
        }

        $join = '';

        $sql = "SET NOCOUNT ON;
        CREATE TABLE #SelectedCampaign(CampaignName varchar(50) Primary Key);
        CREATE TABLE #SelectedRep(RepName varchar(50) Primary Key);";

        // load temp tables
        if (!empty($this->params['campaigns']) && $this->params['campaigns'] != '*') {
            $campaigns = str_replace("'", "''", implode('!#!', $this->params['campaigns']));
            $bind['campaigns'] = $campaigns;

            $join .= "
            INNER JOIN #SelectedCampaign C on C.CampaignName = DR.Campaign";

            $sql .= "
            INSERT INTO #SelectedCampaign SELECT DISTINCT [value] from dbo.SPLIT(:campaigns, '!#!');";
        }

        if (!empty($this->params['reps']) && $this->params['reps'] != '*') {
            $reps = str_replace("'", "''", implode('!#!', $this->params['reps']));
            $bind['reps'] = $reps;

            $join .= "
            INNER JOIN #SelectedRep R on R.RepName COLLATE SQL_Latin1_General_CP1_CS_AS = DR.Rep";

            $sql .= "
            INSERT INTO #SelectedRep SELECT DISTINCT [value] from dbo.SPLIT(:reps, '!#!');";
        }

        $sql .= "
                 SELECT
                    'Date' = $xAxis,
                    'Outbound' = SUM(CASE WHEN DR.CallType NOT IN (1,11) THEN 1 ELSE 0 END),
                    'Inbound' = SUM(CASE WHEN DR.CallType IN (1,11) THEN 1 ELSE 0 END),
                    'Abandoned' = SUM(CASE WHEN DR.CallStatus = 'CR_HANGUP' AND DR.CallType IN (1,11) THEN 1 ELSE 0 END),
                    'Dropped' = SUM(CASE WHEN DR.CallStatus = 'CR_DROPPED' AND DR.CallType NOT IN (1,11) THEN 1 ELSE 0 END),
                    'Connects' = SUM(ISNULL(CASE WHEN DI.Type > 0 THEN 1 ELSE NULL END, 0)),
                    'Contacts' = SUM(ISNULL(CASE WHEN DI.Type > 1 THEN 1 ELSE NULL END, 0)),
                    'Sales' = SUM(ISNULL(CASE WHEN DI.Type = 3 THEN 1 ELSE NULL END, 0)),
                    'TalkTime' = SUM(CASE WHEN DI.Type > 1 THEN DR.Duration ELSE 0 END),
                    'Contacts' = SUM(ISNULL(CASE WHEN DI.Type > 1 THEN 1 ELSE NULL END, 0))
                FROM [DialingResults] DR
                LEFT JOIN [Dispos] DI ON DI.id = DR.DispositionId
                $join
                WHERE $call_type_where
                AND DR.CallStatus NOT IN ('CR_CEPT', 'CR_CNCT/CON_PAMD', 'CR_NOANS',
                    'CR_NORB', 'CR_BUSY', 'CR_FAXTONE',
                    'CR_FAILED', 'CR_DISCONNECTED', 'CR_CNCT/CON_CAD',
                    'CR_CNCT/CON_PVD', '', 'Inbound', 'Inbound Voicemail')
                AND DR.GroupId = :groupid
                AND DR.Date >= :startdate
                AND DR.Date < :enddate
                GROUP BY $xAxis
                ORDER BY Date";

        return [$sql, $bind];
    }

    public function processResults($sql, $bind)
    {
        $results = $this->runSql($sql, $bind);

        $totals = [
            'Date' => strtoupper(trans('reports.total')),
            'TotalCalls' => 0,
            'Connects' => 0,
            'Contacts' => 0,
            'Sales' => 0,
            'ConversionRate' => 0,
            'Inbound' => 0,
            'InboundPct' => 0,
            'Abandoned' => 0,
            'AbandonRate' => 0,
            'Outbound' => 0,
            'OutboundPct' => 0,
            'Dropped' => 0,
            'DropRate' => 0,
            'TalkTime' => 0,
            'ContactRatio' => 0,
            'Contacts' => 0,
        ];
        $final = [];
        $this->extras['Date'] = [];
        $this->extras['Abandoned'] = [];
        $this->extras['Dropped'] = [];

        foreach ($results as $rec) {
            $totalcalls = $rec['Inbound'] + $rec['Outbound'];

            $data = [
                'Date' => Carbon::parse($rec['Date'])->isoFormat('L ha'),
                'TotalCalls' => $totalcalls,
                'Connects' => $rec['Connects'],
                'Contacts' => $rec['Contacts'],
                'Sales' => $rec['Sales'],
                'ConversionRate' => number_format(($rec['Contacts'] > 0) ? $rec['Sales'] / $rec['Contacts'] * 100 : 0, 2) . '%',
                'Inbound' => $rec['Inbound'],
                'InboundPct' => number_format($rec['Inbound'] / $totalcalls * 100, 2) . '%',
                'Abandoned' => $rec['Abandoned'],
                'AbandonRate' => number_format($rec['Inbound'] == 0 ? 0 : $rec['Abandoned'] / $rec['Inbound'] * 100, 2) . '%',
                'Outbound' => $rec['Outbound'],
                'OutboundPct' => number_format($rec['Outbound'] / $totalcalls * 100, 2) . '%',
                'Dropped' => $rec['Dropped'],
                'DropRate' => number_format($rec['Outbound'] == 0 ? 0 : $rec['Dropped'] / $rec['Outbound'] * 100, 2) . '%',
                'TalkTime' => $this->secondsToHms($rec['TalkTime']),
                'ContactRatio' => number_format($rec['Contacts'] / $totalcalls * 100, 2) . '%',
            ];

            $this->extras['Date'][] = $data['Date'];
            $this->extras['Inbound'][] = $data['Inbound'];
            $this->extras['Outbound'][] = $data['Outbound'];
            $this->extras['Abandoned'][] = $data['Abandoned'];
            $this->extras['Dropped'][] = $data['Dropped'];

            $final[] = $data;

            $totals['TotalCalls'] += $data['TotalCalls'];
            $totals['Connects'] += $data['Connects'];
            $totals['Contacts'] += $data['Contacts'];
            $totals['Sales'] += $data['Sales'];
            $totals['Abandoned'] += $data['Abandoned'];
            $totals['Dropped'] += $data['Dropped'];
            $totals['Inbound'] += $data['Inbound'];
            $totals['Outbound'] += $data['Outbound'];
            $totals['TalkTime'] += $rec['TalkTime'];  // raw number
            $totals['Contacts'] += $rec['Contacts'];  // raw number
        }

        if ($totals['TotalCalls'] > 0) {
            $totals['TalkTime'] = $this->secondsToHms($totals['TalkTime']);
            $totals['ConversionRate'] = number_format(($totals['Contacts'] > 0) ? $totals['Sales'] / $totals['Contacts'] * 100 : 0, 2) . '%';
            $totals['AbandonRate'] = number_format($totals['Inbound'] == 0 ? 0 : $totals['Abandoned'] / $totals['Inbound'] * 100, 2) . '%';
            $totals['DropRate'] = number_format($totals['Outbound'] == 0 ? 0 : $totals['Dropped'] / $totals['Outbound'] * 100, 2) . '%';
            $totals['InboundPct'] = number_format($totals['Inbound'] / $totals['TotalCalls'] * 100, 2) . '%';
            $totals['OutboundPct'] = number_format($totals['Outbound'] / $totals['TotalCalls'] * 100, 2) . '%';
            $totals['ContactRatio'] = number_format($totals['Contacts'] / $totals['TotalCalls'] * 100, 2) . '%';

            $final[] = $totals;
        }

        return $final;
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
        }

        if (!empty($request->reps)) {
            $this->params['reps'] = $request->reps;
        }

        if (!empty($request->call_type)) {
            $this->params['call_type'] = $request->call_type;
        }

        // Save params to session
        $this->saveSessionParams();

        return $this->errors;
    }
}
