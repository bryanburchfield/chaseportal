<?php

namespace App\Services\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use \App\Traits\ReportTraits;
use Illuminate\Support\Carbon;

class AgentActivity
{
    use ReportTraits;

    public function __construct()
    {
        $this->initilaizeParams();

        $this->params['reportName'] = 'reports.agent_activity';
        $this->params['reps'] = '';
        $this->params['columns'] = [
            'Rep' => 'reports.rep',
            'Campaign' => 'reports.campaign',
            'Event' => 'reports.event',
            'Duration' => 'reports.duration',
            'Date' => 'reports.date',
            'Details' => 'reports.details',
        ];
    }

    public function getFilters()
    {
        $filters = [
            'reps' => $this->getAllReps(),
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
        array_pop($rec);
        $rec['Date'] = Carbon::parse($rec['Date'])->isoFormat('L LT');
        $rec['Duration'] = $this->secondsToHms($rec['Duration']);
        $this->rowclass[] = 'agentcalllog_' . Str::snake($rec['Event']);

        return $rec;
    }

    public function makeQuery($all)
    {
        $this->setHeadings();

        list($fromDate, $toDate) = $this->dateRange($this->params['fromdate'], $this->params['todate']);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');
        $reps = '';
        if (!empty($this->params['reps'])) {
            $reps = str_replace("'", "''", implode('!#!', $this->params['reps']));
        }

        $tz =  Auth::user()->tz;

        $sql = "SET NOCOUNT ON;

        SELECT * INTO #AgentLog FROM (";

        $union = '';
        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id' . $i] = Auth::user()->group_id;
            $bind['startdate' . $i] = $startDate;
            $bind['enddate' . $i] = $endDate;
            $bind['reps1' . $i] = $reps;
            $bind['reps2' . $i] = $reps;

            $sql .= " $union SELECT
        CONVERT(datetimeoffset, Date) AT TIME ZONE '$tz' as Date,
        Campaign,
        [Action],
        Duration,
        IsNull(Details, '') as Details,
        IsNull(Rep, '') as Rep
    FROM [$db].[dbo].[AgentActivity] WITH(NOLOCK)
    WHERE GroupId = :group_id$i
    AND	Date >= :startdate$i
    AND Date < :enddate$i
    AND	(:reps1$i = '' OR Rep in (SELECT value COLLATE SQL_Latin1_General_CP1_CS_AS FROM dbo.SPLIT(:reps2$i, '!#!')))
    AND	(([Action] = 'Paused' AND Duration > 30) OR ([Action] <> 'Paused'))";

            if (session('ssoRelativeCampaigns', 0)) {
                $sql .= " AND Campaign IN (SELECT CampaignName FROM dbo.GetAllRelativeCampaigns(:ssousercamp$i, 1))";
                $bind['ssousercamp' . $i] = session('ssoUsername');
            }

            if (session('ssoRelativeReps', 0)) {
                $sql .= " AND Rep IN (SELECT RepName FROM dbo.GetAllRelativeReps(:ssouserrep$i))";
                $bind['ssouserrep' . $i] = session('ssoUsername');
            }

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
    Duration,
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

        return [$sql, $bind];
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

        // Save params to session
        $this->saveSessionParams();

        return $this->errors;
    }
}
