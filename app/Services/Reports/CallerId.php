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

        $this->params['reportName'] = 'reports.caller_id';
        $this->params['caller_id'] = '';
        $this->params['hasTotals'] = true;
        $this->params['columns'] = [
            'CallerId' => 'reports.callerid',
            'Total' => 'reports.totalcalls',
            'Agent' => 'reports.agent',
            'ConnectPct' => 'reports.connectpct',
            'PrevCalls' => 'reports.last_30_days',
        ];
    }

    public function getFilters()
    {
        $filters = [
            'db_list' => Auth::user()->getDatabaseArray(),
        ];

        return $filters;
    }

    public function getInfo()
    {
        return [
            'columns' => $this->params['columns'],
            'paragraphs' => 2,
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
            $results = [];
        } else {
            $this->params['totrows'] = count($results);
            $this->params['totpages'] = floor($this->params['totrows'] / $this->params['pagesize']);
            $this->params['totpages'] += floor($this->params['totrows'] / $this->params['pagesize']) == ($this->params['totrows'] / $this->params['pagesize']) ? 0 : 1;
            $results = $this->processResults($results);
        }

        $page = $this->getPage($results, $all);
        $this->createExtras($page);

        return $page;
    }

    public function makeQuery($all)
    {
        $this->setHeadings();

        list($fromDate, $toDate) = $this->dateRange($this->params['fromdate'], $this->params['todate']);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $bind = [
            'group_id1' => Auth::user()->group_id,
            'group_id2' => Auth::user()->group_id,
            'startdate' => $startDate,
            'enddate' => $endDate,
            'prevstart' => now()->subDays(30)->format('Y-m-d H:i:s'),
            'prevend' => now()->format('Y-m-d H:i:s'),
        ];

        // Have to hard-code what's considered 'system' for connect calculations
        $system_codes = $this->systemCodeList();

        $sql = "SET NOCOUNT ON;

CREATE TABLE #Summary
(
  CallerId varchar(50),
  Total int default 0,
  Agent int default 0,
  ConnectPct numeric(18,2) default 0,
  PrevCalls int default 0
)

INSERT INTO #Summary (CallerId, Total, Agent, ConnectPct)
SELECT
    [CallerId],
	COUNT(CallerId) as [Total],
	SUM(Agent) as Agent,
	SUM(Agent) / COUNT(CallerId) * 100 as ConnectPct
FROM (
	SELECT
		CallerId,
		CASE WHEN CallStatus NOT IN ($system_codes) THEN 1.0 ELSE 0 END as Agent
	FROM DialingResults
	WHERE GroupId = :group_id1
	AND CallDate >= :startdate
	AND CallDate < :enddate
	AND CallerId != ''
    AND CallType IN (0,2)
    AND CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD', 'Inbound')";

        if (!empty($this->params['caller_id'])) {
            $bind['caller_id'] = $this->params['caller_id'] . '%';
            $sql .= " AND CallerId LIKE :caller_id";
        }

        if (session('ssoRelativeCampaigns', 0)) {
            $sql .= " AND Campaign IN (SELECT CampaignName FROM dbo.GetAllRelativeCampaigns(:ssousercamp, 1))";
            $bind['ssousercamp'] = session('ssoUsername');
        }

        if (session('ssoRelativeReps', 0)) {
            $sql .= " AND Rep IN (SELECT RepName FROM dbo.GetAllRelativeReps(:ssouserrep))";
            $bind['ssouserrep'] = session('ssoUsername');
        }

        $sql .= "
) tmp
GROUP BY CallerID

UPDATE #Summary
SET PrevCalls = a.PrevCalls
FROM (
    SELECT CallerId, COUNT(*) as PrevCalls
	FROM DialingResults
	WHERE GroupId = :group_id2
	AND CallDate >= :prevstart
	AND CallDate < :prevend
	AND CallerId != ''
    AND CallType IN (0,2)
    AND CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD', 'Inbound')
	GROUP BY CallerId
) a
WHERE #Summary.CallerId = a.CallerId 

SELECT * FROM #Summary";

        // Check params
        if (!empty($this->params['orderby']) && is_array($this->params['orderby'])) {
            $sort = '';
            foreach ($this->params['orderby'] as $col => $dir) {
                $sort .= ",$col $dir";
            }
            $sql .= ' ORDER BY ' . substr($sort, 1);
        } else {
            $sql .= ' ORDER BY [Total] DESC, CallerId';
        }

        return [$sql, $bind];
    }

    private function processResults($results)
    {
        // this sets the order of the columns
        foreach ($this->params['columns'] as $k => $v) {
            $total[$k] = '';
        }

        $total = [
            'CallerId' => 'Total:',
            'Total' => 0,
            'Agent' => 0,
            'ConnectPct' => 0,
            'PrevCalls' => 0,
        ];

        foreach ($results as &$rec) {
            $rec = $this->processRow($rec);
            $total['Total'] += $rec['Total'];
            $total['Agent'] += $rec['Agent'];
            $total['PrevCalls'] += $rec['PrevCalls'];
        }

        // format totals
        $total['ConnectPct'] = number_format($total['Agent'] / $total['Total'] * 100, 2);

        // Tack on the totals row
        $results[] = $total;

        return $results;
    }

    public function processRow($rec)
    {
        $rec['ConnectPct'] = number_format($rec['ConnectPct'], 2);

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

        if (!empty($request->caller_id)) {
            $this->params['caller_id'] = $request->caller_id;
        }

        // Save params to session
        $this->saveSessionParams();

        return $this->errors;
    }

    private function createExtras($results)
    {
        $this->extras['callerid'] = [];
        $this->extras['agent'] = [];
        $this->extras['total'] = [];
        $this->extras['system'] = [];

        if (!count($results)) {
            return;
        }

        array_pop($results); // remove totals row

        foreach ($results as $rec) {
            $this->extras['callerid'][] = $rec['CallerId'];
            $this->extras['agent'][] = (int) $rec['Agent'];
            $this->extras['total'][] = (int) $rec['Total'];
            $this->extras['system'][] = $rec['Total'] - $rec['Agent'];
        }
    }
}
