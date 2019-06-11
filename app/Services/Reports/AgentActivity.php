<?php

namespace App\Services\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;


class AgentActivity
{
    use ReportTraits;

    public function __construct()
    {
        $this->initilaizeParams();

        $this->params['fromdate'] = '';
        $this->params['todate'] = '';
        $this->params['reps'] = '';
        $this->params['columns'] = [
            'Rep' => 'Rep',
            'Campaign' => 'Campaign',
            'Event' => 'Event',
            'Date' => 'Date',
            'Details' => 'Details',
        ];
    }

    public function getFilters()
    {
        $filters = [
            'reps' => $this->getAllReps(true),
        ];

        return $filters;
    }

    private function executeReport($all = false)
    {
        // Log::debug($this->params);
        list($fromDate, $toDate) = $this->dateRange($this->params['fromdate'], $this->params['todate']);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');
        $reps = '';
        if (!empty($this->params['reps'])) {
            $reps = str_replace("'", "''", implode('!#!', $this->params['reps']));
        }

        $tz =  Auth::user()->tz;
        $bind['group_id'] = Auth::user()->group_id;
        $bind['startdate'] = $startDate;
        $bind['enddate'] = $endDate;
        $bind['reps1'] = $reps;
        $bind['reps2'] = $reps;

        $sql = "SET NOCOUNT ON;
    
        SELECT * INTO #AgentLog FROM (";

        $union = '';
        foreach (Auth::user()->getDatabaseArray() as $db) {
            $sql .= " $union SELECT
        CONVERT(datetimeoffset, Date) AT TIME ZONE '$tz' as Date,
        Campaign,
        [Action],
        Duration,
        IsNull(Details, '') as Details,
        IsNull(Rep, '') as Rep
    FROM [$db].[dbo].[AgentActivity] WITH(NOLOCK)
    WHERE GroupId = :group_id
    AND	Date >= :startdate
    AND Date < :enddate
    AND	(:reps1 = '' OR Rep in (SELECT value COLLATE SQL_Latin1_General_CP1_CS_AS FROM dbo.SPLIT(:reps2, '!#!')))
    AND	(([Action] = 'Paused' AND Duration > 30) OR ([Action] <> 'Paused'))";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp;
    
    CREATE INDEX IX_CampaignAction ON #AgentLog (Campaign, [Action], Date);
    CREATE INDEX IX_Action ON #AgentLog ([Action]);
    
    UPDATE #AgentLog SET [Action] = 'Unpaused' WHERE [Action] = 'Paused';
    
    INSERT INTO #AgentLog(Campaign, Date, [Action], Duration, Details, Rep)
    SELECT Campaign, dateadd(ss, -1*Duration, Date), 'Paused', 0, Details, Rep
    FROM #AgentLog WHERE [Action] = 'Unpaused';
    
    SELECT Rep,
    Campaign,
    [Action] as Event,
    Date,
    Details,
    totRows = COUNT(*) OVER()
    FROM #AgentLog";

        // Check params
        if (!empty($this->params['orderby']) && is_array($this->params['orderby'])) {
            $sort = '';
            foreach ($this->params['orderby'] as $col => $dir) {
                $sort .= ",$col $dir";
            }
            $sql .= ' ORDER BY ' . substr($sort, 1);
        } else {
            $sql .= ' ORDER BY Rep, Date, [Action]';
        }

        if (!$all) {
            $offset = ($this->params['curpage'] - 1) * $this->params['pagesize'];
            $sql .= " OFFSET $offset ROWS FETCH NEXT " . $this->params['pagesize'] . " ROWS ONLY";
        }

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
                $this->rowclass[] = 'agentcalllog_' . Str::snake($rec['Event']);
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

        if (!empty($request->reps)) {
            $this->params['reps'] = $request->reps;
        }

        return $this->errors;
    }
}
