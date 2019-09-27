<?php

namespace App\Services\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\ReportTraits;

class CallerId
{
    use ReportTraits;

    public function __construct()
    {
        $this->initilaizeParams();

        $this->params['reportName'] = 'Caller ID Report';
        $this->params['fromdate'] = date("m/d/Y 9:00 \A\M");
        $this->params['todate'] = date("m/d/Y 8:00 \P\M");
        $this->params['caller_id'] = '';
        $this->params['hasTotals'] = true;
        $this->params['columns'] = [
            'CallerId' => 'Caller ID',
            'Total' => 'Total Calls',
            'Agent' => 'Agent Calls',
            'ConnectPct' => 'Connect %',
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
        list($fromDate, $toDate) = $this->dateRange($this->params['fromdate'], $this->params['todate']);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $bind = [];

        $sql = "SET NOCOUNT ON;

        SELECT CallerId,
        'Total' = COUNT(CallerId),
        'Agent' = SUM(Agent),
        'ConnectPct' = SUM(Agent) * 10000 / COUNT(CallerId)
        FROM (";

        $union = '';
        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id' . $i] =  Auth::user()->group_id;
            $bind['startdate' . $i] = $startDate;
            $bind['enddate' . $i] = $endDate;

            $sql .= " $union SELECT
            CallerId,
            'Agent' = CASE WHEN CallStatus NOT LIKE 'CR_%' THEN 1 ELSE 0 END,
            'Total' = 1
            FROM [$db].[dbo].[DialingResults]
            WHERE GroupId = :group_id$i
            AND Date >= :startdate$i
            AND Date < :enddate$i
            AND CallerId != ''
            AND CallType NOT IN (1,4,5,6,7,8,11,14)
            AND CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD', 'Inbound')";

            if (!empty($this->params['caller_id'])) {
                $bind['caller_id' . $i] = $this->params['caller_id'] . '%';
                $sql .= " AND CallerId LIKE :caller_id$i";
            }

            $union = 'UNION ALL';
        }

        $sql .= ") tmp
        GROUP BY CallerID";

        // Check params
        if (!empty($this->params['orderby']) && is_array($this->params['orderby'])) {
            $sort = '';
            foreach ($this->params['orderby'] as $col => $dir) {
                $sort .= ",$col $dir";
            }
            $sql .= ' ORDER BY ' . substr($sort, 1);
        } else {
            $sql .= ' ORDER BY [Total] DESC';
        }

        $results = $this->runSql($sql, $bind);

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

        $results = $this->processResults($results);

        $page = $this->getPage($results, $all);
        $this->createExtras($page);

        return $page;
    }

    private function processResults($results)
    {
        // this sets the order of the columns
        foreach ($this->params['columns'] as $k => $v) {
            $total[$k] = '';
        }

        $total['CallerId'] = 'Total:';
        $total['Total'] = 0;
        $total['Agent'] = 0;
        $total['ConnectPct'] = 0;

        foreach ($results as &$rec) {
            $rec['ConnectPct'] = number_format($rec['ConnectPct'] / 100, 2);
            $total['Total'] += $rec['Total'];
            $total['Agent'] += $rec['Agent'];
        }

        // format totals
        $total['ConnectPct'] = number_format($total['Agent'] / $total['Total'] * 100, 2);

        // Tack on the totals row
        $results[] = $total;

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

        if (!empty($request->callerid)) {
            $this->params['callerid'] = $request->callerid;
        }

        // Save params to session
        $this->saveSessionParams();

        return $this->errors;
    }

    private function createExtras($results)
    {
        if (!count($results)) {
            return;
        }

        array_pop($results); // remove totals row

        $this->extras['callerid'] = array_column($results, 'CallerId');
        $this->extras['agent'] = array_column($results, 'Agent');
        $this->extras['total'] = array_column($results, 'Total');
    }
}
