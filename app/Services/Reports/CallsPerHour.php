<?php

namespace App\Services\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\ReportTraits;
use Illuminate\Support\Carbon;

class CallsPerHour
{
    use ReportTraits;

    public function __construct()
    {
        $this->initilaizeParams();

        $this->params['reportName'] = 'reports.calls_per_hour';
        $this->params['nostreaming'] = true;
        $this->params['columns'] = [
            'Date' => 'reports.hour',
            'TotalCalls' => 'reports.totalcalls',
            'Sales' => 'reports.sales',
            'ConversionRate' => 'reports.conversionrate',
            'Abandoned' => 'reports.abandoned',
            'AbandonRate' => 'reports.abandonrate',
            'Inbound' => 'reports.inbound',
            'InboundPct' => 'reports.inbound_pct',
            'Outbound' => 'reports.outbound',
            'OutboundPct' => 'reports.outbound_pct',
            'TalkTime' => 'reports.talktimesec',
            'ContactRatio' => 'reports.contactpct',
        ];
    }

    public function getFilters()
    {
        $filters = [
            'db_list' => Auth::user()->getDatabaseArray(),
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

        $sql = "SELECT
                    'Date' = $xAxis,
                    'Outbound' = SUM(CASE WHEN DR.CallType NOT IN (1,11) THEN 1 ELSE 0 END),
                    'Inbound' = SUM(CASE WHEN DR.CallType IN (1,11) THEN 1 ELSE 0 END),
                    'Abandoned' = SUM(CASE WHEN DR.CallStatus = 'CR_HANGUP' THEN 1 ELSE 0 END),
                    'Sales' = SUM(ISNULL(CASE WHEN DI.Type = 3 THEN 1 ELSE NULL END, 0)),
                    'TalkTime' = SUM(CASE WHEN DI.Type > 1 THEN DR.Duration ELSE 0 END),
                    'Contacts' = SUM(ISNULL(CASE WHEN DI.Type > 1 THEN 1 ELSE NULL END, 0))
                FROM [DialingResults] DR
                OUTER APPLY (SELECT TOP 1 [Type]
                    FROM  [Dispos]
                    WHERE Disposition = DR.CallStatus
                    AND (GroupId = DR.GroupId OR IsSystem=1)
                    AND (Campaign = DR.Campaign OR Campaign = '')
                    ORDER BY [id]) DI
                WHERE DR.CallType NOT IN (7,8)
                --AND DR.Duration > 0
                AND DR.CallStatus NOT IN ('CR_CEPT', 'CR_CNCT/CON_PAMD', 'CR_NOANS',
                    'CR_NORB', 'CR_BUSY', 'CR_DROPPED', 'CR_FAXTONE',
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
            'Sales' => 0,
            'ConversionRate' => 0,
            'Abandoned' => 0,
            'AbandonRate' => 0,
            'Inbound' => 0,
            'InboundPct' => 0,
            'Outbound' => 0,
            'OutboundPct' => 0,
            'TalkTime' => 0,
            'ContactRatio' => 0,
            'Contacts' => 0,
        ];
        $final = [];

        foreach ($results as $rec) {
            $totalcalls = $rec['Inbound'] + $rec['Outbound'];

            $data = [
                'Date' => Carbon::parse($rec['Date'])->isoFormat('L ha'),
                'TotalCalls' => $totalcalls,
                'Sales' => $rec['Sales'],
                'ConversionRate' => number_format($rec['Sales'] / $totalcalls * 100, 2) . '%',
                'Abandoned' => $rec['Abandoned'],
                'AbandonRate' => number_format($rec['Abandoned'] / $totalcalls * 100, 2) . '%',
                'Inbound' => $rec['Inbound'],
                'InboundPct' => number_format($rec['Inbound'] / $totalcalls * 100, 2) . '%',
                'Outbound' => $rec['Outbound'],
                'OutboundPct' => number_format($rec['Outbound'] / $totalcalls * 100, 2) . '%',
                'TalkTime' => $this->secondsToHms($rec['TalkTime']),
                'ContactRatio' => number_format($rec['Contacts'] / $totalcalls * 100, 2) . '%',
            ];

            $final[] = $data;

            $totals['TotalCalls'] += $data['TotalCalls'];
            $totals['Sales'] += $data['Sales'];
            $totals['Abandoned'] += $data['Abandoned'];
            $totals['Inbound'] += $data['Inbound'];
            $totals['Outbound'] += $data['Outbound'];
            $totals['TalkTime'] += $rec['TalkTime'];  // raw number
            $totals['Contacts'] += $rec['Contacts'];  // raw number
        }

        $totals['TalkTime'] = $this->secondsToHms($totals['TalkTime']);
        $totals['ConversionRate'] = number_format($totals['Sales'] / $totals['TotalCalls'] * 100, 2) . '%';
        $totals['AbandonRate'] = number_format($totals['Abandoned'] / $totals['TotalCalls'] * 100, 2) . '%';
        $totals['InboundPct'] = number_format($totals['Inbound'] / $totals['TotalCalls'] * 100, 2) . '%';
        $totals['OutboundPct'] = number_format($totals['Outbound'] / $totals['TotalCalls'] * 100, 2) . '%';
        $totals['ContactRatio'] = number_format($totals['Contacts'] / $totals['TotalCalls'] * 100, 2) . '%';

        unset($totals['Contacts']);

        $final[] = $totals;

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

        // Save params to session
        $this->saveSessionParams();

        return $this->errors;
    }
}
