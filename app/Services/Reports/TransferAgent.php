<?php

namespace App\Services\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\ReportTraits;

class TransferAgent
{
    use ReportTraits;

    public function __construct()
    {
        $this->initilaizeParams();

        $this->params['reportName'] = 'reports.transfer_agent';
        $this->params['hasTotals'] = true;
        $this->params['columns'] = [
            'Rep' => 'reports.rep',
            'Closer' => 'reports.closer',
            'Date' => 'reports.date',
            'Phone' => 'reports.phone',
            'CallStatus' => 'reports.callstatus',
        ];
    }

    public function getFilters()
    {
        $filters = [
            'reps' => $this->getAllReps(),
            'closers' => $this->getAllReps(),
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

        $bind = [];

        $sql = "SET NOCOUNT ON;

        CREATE TABLE #SelectedRep(RepName varchar(50) Primary Key);
        CREATE TABLE #SelectedCloser(RepName varchar(50) Primary Key);";

        if (!empty($this->params['reps'])) {
            $reps = str_replace("'", "''", implode('!#!', $this->params['reps']));
            $bind['reps'] = $reps;

            $sql .= "
            INSERT INTO #SelectedRep SELECT DISTINCT [value] from dbo.SPLIT(:reps, '!#!');";
        }

        if (!empty($this->params['closers'])) {
            $closers = str_replace("'", "''", implode('!#!', $this->params['closers']));
            $bind['closers'] = $closers;

            $sql .= "
            INSERT INTO #SelectedCloser SELECT DISTINCT [value] from dbo.SPLIT(:closers, '!#!');";
        }

        $sql .= "
        SELECT Rep, Closer, Date, Phone, CallStatus,
        CASE WHEN Type = 3 THEN 1 ELSE 0 END as Sales
        FROM (";

        $union = '';
        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id' . $i] = Auth::user()->group_id;
            $bind['startdate' . $i] = $startDate;
            $bind['enddate' . $i] = $endDate;

            $sql .= " $union SELECT DR.Date, DR.PreviousRep as Rep, DR.Rep as Closer, DR.Phone, DR.CallStatus, DI.Type
            FROM [$db].[dbo].[DialingResults] DR
            INNER JOIN Reps R ON R.RepName = DR.Rep
            INNER JOIN Reps PR ON PR.RepName = DR.PreviousRep";

            if (!empty($this->params['reps'])) {
                $sql .= "
                INNER JOIN #SelectedRep SR on SR.RepName COLLATE SQL_Latin1_General_CP1_CS_AS = DR.PreviousRep";
            }

            if (!empty($this->params['closers'])) {
                $sql .= "
                INNER JOIN #SelectedCloser SC on SC.RepName COLLATE SQL_Latin1_General_CP1_CS_AS = DR.Rep";
            }

            $sql .= "
            LEFT JOIN Dispos DI ON DI.id = DR.DispositionId
            WHERE DR.GroupId = :group_id$i
            AND DR.Date >= :startdate$i
            AND DR.Date < :enddate$i
            AND DR.Rep != DR.PreviousRep
            AND Duration > 0";

            if (session('ssoRelativeCampaigns', 0)) {
                $sql .= " AND DR.Campaign IN (SELECT CampaignName FROM dbo.GetAllRelativeCampaigns(:ssousercamp$i, 1))";
                $bind['ssousercamp' . $i] = session('ssoUsername');
            }

            if (session('ssoRelativeReps', 0)) {
                $sql .= " AND DR.Rep IN (SELECT RepName FROM dbo.GetAllRelativeReps(:ssouserrep$i))";
                $bind['ssouserrep' . $i] = session('ssoUsername');
            }

            $union = 'UNION';
        }

        $sql .= ") tmp
        ORDER BY Rep, Closer, Date";

        return [$sql, $bind];
    }

    private function processResults($results)
    {
        $final = [];

        // this sets the order of the columns
        foreach ($this->params['columns'] as $k => $v) {
            $subtotal[$k] = '';
            $total[$k] = '';
        }

        $subtotal = $this->zeroRec($subtotal);
        $total = $this->zeroRec($total);

        $oldrep = '';

        foreach ($results as $rec) {
            $total = $this->addTotals($total, $rec);

            if ($rec['Rep'] != $oldrep && $oldrep != '') {
                $final[] = $this->processSubTotal($subtotal);
                $subtotal = $this->zeroRec($subtotal);
            }

            $oldrep = $rec['Rep'];
            $subtotal = $this->addTotals($subtotal, $rec);

            $final[] = $this->processRow($rec);
        }

        // Tack on the totals row
        $final[] = $this->processSubTotal($subtotal);
        $final[] = $this->processTotal($total);

        return $final;
    }

    public function processRow($rec)
    {
        unset($rec['Sales']);

        $rec['Date'] = $this->utcToLocal($rec['Date'], Auth::user()->iana_tz)->isoFormat('L LT');

        return $rec;
    }

    private function zeroRec($rec)
    {
        $rec['Calls'] = 0;
        $rec['Sales'] = 0;
        $rec['ClosingPct'] = 0;

        return $rec;
    }

    private function addTotals($total, $rec)
    {
        $total['Calls']++;
        $total['Sales'] += $rec['Sales'];

        return $total;
    }

    private function processSubTotal($total)
    {
        $total = $this->processTotal($total);
        $total['isSubtotal'] = 1;

        return $total;
    }

    private function processTotal($total)
    {
        // calc total avgs
        $total['ClosingPct'] = $total['Calls'] == 0 ? 0 : number_format($total['Sales'] / $total['Calls'] * 100, 2) . '%';

        // format totals
        $total['Date'] = 'Count: ' . $total['Calls'];
        $total['Phone'] = 'Sales: ' . $total['Sales'];
        $total['CallStatus'] = 'Closing Pct: ' . $total['ClosingPct'];

        // unset unused cols
        unset($total['Calls']);
        unset($total['Sales']);
        unset($total['ClosingPct']);

        return $total;
    }

    private function processInput(Request $request)
    {
        // Get vals from session if not set (for exports)
        $request = $this->getSessionParams($request);

        // Check page filters
        $this->checkPageFilters($request);

        // Check report filters
        $this->checkDateRangeFilters($request);

        if (!empty($request->reps)) {
            $this->params['reps'] = $request->reps;
        }

        if (!empty($request->closers)) {
            $this->params['closers'] = $request->closers;
        }

        // Save params to session
        $this->saveSessionParams();

        return $this->errors;
    }
}
