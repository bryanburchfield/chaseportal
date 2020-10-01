<?php

namespace App\Services\Reports;

use App\Models\Dialer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\ReportTraits;

class GroupDuration
{
    use ReportTraits;

    public function __construct()
    {
        $this->initilaizeParams();

        $this->params['reportName'] = 'reports.group_duration';
        $this->params['dialer'] = '';
        $this->params['groups'] = [];
        $this->params['columns'] = [
            'GroupId' => 'reports.group_id',
            'GroupName' => 'reports.name',
            'Duration' => 'reports.minutes',
            'InboundNumbers' => 'reports.inbound_numbers',
            'TollFreeNumbers' => 'reports.toll_free_numbers',
            'Inbound' => 'reports.inbound',
            'Manual' => 'reports.manual',
            'Conference' => 'reports.conference',
            'MaxSeats' => 'reports.max_seats',
            'AvgSeats' => 'reports.avg_seats',
            'RealAvgSeats' => 'reports.real_avg_seats',
        ];
    }

    public function getFilters()
    {
        // Bail if user not allowed here
        if (!(Auth::User()->isType('superadmin') ||
            Auth::User()->reportPermissions()->where('report_name', 'group_duration')->exists())) {
            abort(404);
        }

        $dialers = [];
        foreach (Dialer::pluck('reporting_db')->all() as $dialer) {
            $dialers[$dialer] = $dialer;
        }

        $filters = [
            'dialers' => $dialers,
            'groups' => [],
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
        // Bail if user not allowed here (exports start here)
        if (!(Auth::User()->isType('superadmin') ||
            Auth::User()->reportPermissions()->where('report_name', 'group_duration')->exists())) {
            abort(404);
        }

        // set db based on input
        $curr_db = Auth::user()->dialer->reporting_db;
        Auth::user()->dialer->reporting_db = $this->params['dialer'];

        list($sql, $bind) = $this->makeQuery($all);

        $results = $this->runSql($sql, $bind);

        if (empty($results)) {
            $this->params['totrows'] = 0;
            $this->params['totpages'] = 1;
            $this->params['curpage'] = 1;
        } else {
            $this->params['totrows'] = count($results);

            foreach ($results as &$rec) {
                $rec = $this->processRow($rec);
            }
            $this->params['totpages'] = floor($this->params['totrows'] / $this->params['pagesize']);
            $this->params['totpages'] += floor($this->params['totrows'] / $this->params['pagesize']) == ($this->params['totrows'] / $this->params['pagesize']) ? 0 : 1;
        }

        // set user's db back to what it was
        Auth::user()->dialer->reporting_db = $curr_db;

        return $results;
    }

    public function makeQuery($all)
    {
        $this->setHeadings();

        list($fromDate, $toDate) = $this->dateRange($this->params['fromdate'], $this->params['todate']);

        $timeZoneName = Auth::user()->tz;

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $groups = implode(',', $this->params['groups']);

        $bind = [];

        for ($i = 1; $i <= 5; $i++) {
            $bind['startdate' . $i] = $startDate;
            $bind['enddate' . $i] = $endDate;
        }

        $sql = "SET NOCOUNT ON;
        
CREATE TABLE #GroupStatistics
(
    GroupId int PRIMARY KEY,
    GroupName varchar(50),
    Duration int DEFAULT 0,
    InboundNumbers int DEFAULT 0,
    TollFreeNumbers int DEFAULT 0,
    Inbound int DEFAULT 0,
    Manuals int DEFAULT 0,
    Conference int DEFAULT 0,
    MaxSeats int DEFAULT 0,
    AvgSeats int DEFAULT 0,
    RealAvgSeats int DEFAULT 0
)

INSERT #GroupStatistics (GroupId, GroupName)
SELECT g.GroupId, g.GroupName
FROM Groups g
WHERE g.GroupId IN ($groups)

UPDATE #GroupStatistics
    SET InboundNumbers = t.Numbers
FROM
    (SELECT i.GroupId, COUNT(*) as Numbers
    FROM InboundSources i
    GROUP BY i.GroupId) t
WHERE #GroupStatistics.GroupId = t.GroupId

UPDATE #GroupStatistics
    SET TollFreeNumbers = t.Numbers
FROM
    (SELECT i.GroupId, COUNT(*) as Numbers
    FROM InboundSources i
    WHERE LEFT(InboundSource, 3) IN ('800', '888', '877', '866', '855', '844')
    GROUP BY i.GroupId) t
WHERE #GroupStatistics.GroupId = t.GroupId

UPDATE #GroupStatistics
    SET Duration = t.Duration
FROM
    (SELECT dr.GroupId, sum(dr.Duration)/60 as Duration
    FROM DialingResults dr WITH(NOLOCK)
    WHERE dr.CallDate >= :startdate1
    AND dr.CallDate < :enddate1
    GROUP BY dr.GroupId) t
WHERE #GroupStatistics.GroupId = t.GroupId

UPDATE #GroupStatistics
    SET Inbound = t.Duration
FROM
    (SELECT dr.GroupId, sum(dr.Duration)/60 as Duration
    FROM DialingResults dr WITH(NOLOCK)
    WHERE dr.CallDate >= :startdate2
    AND dr.CallDate < :enddate2
    AND CallType = 1
    GROUP BY dr.GroupId) t
WHERE #GroupStatistics.GroupId = t.GroupId

UPDATE #GroupStatistics
    SET Manuals = t.Duration
FROM
    (SELECT dr.GroupId, sum(dr.Duration)/60 as Duration
    FROM DialingResults dr WITH(NOLOCK)
    WHERE dr.CallDate >= :startdate3
    AND dr.CallDate < :enddate3
    AND CallType = 2
    GROUP BY dr.GroupId) t
WHERE #GroupStatistics.GroupId = t.GroupId

UPDATE #GroupStatistics
    SET Conference = t.Duration
FROM
    (SELECT dr.GroupId, sum(dr.Duration)/60 as Duration
    FROM DialingResults dr WITH(NOLOCK)
    WHERE dr.CallDate >= :startdate4
    AND dr.CallDate < :enddate4
    AND CallType = 4
    GROUP BY dr.GroupId) t
WHERE #GroupStatistics.GroupId = t.GroupId

UPDATE #GroupStatistics SET
    MaxSeats = j.MaxSeats,
    RealAvgSeats = j.AvgSeats
FROM
    (SELECT s.GroupId, MAX(s.Seats) as MaxSeats, AVG(s.Seats) as AvgSeats
    FROM
        (SELECT t.GroupId, t.CallTime, count(*) as Seats
        FROM
            (SELECT
                CONVERT(varchar(19), dateadd(minute, datediff(minute,0, CONVERT(datetimeoffset, Date) AT TIME ZONE '$timeZoneName') / 15 * 15, 0),120) as CallTime,
                dr.Rep, dr.GroupId
            FROM DialingResults dr WITH(NOLOCK)
            WHERE IsNull(dr.Rep, '') <> ''
            AND IsNull(dr.CallStatus, '') NOT IN ('', 'Inbound Voicemail', 'CR_HANGUP')
            AND dr.CallDate >= :startdate5
            AND dr.CallDate < :enddate5
            AND dr.CallType NOT IN (6, 7, 8)
            GROUP BY
                CONVERT(varchar(19), dateadd(minute, datediff(minute,0, CONVERT(datetimeoffset, Date) AT TIME ZONE '$timeZoneName') / 15 * 15, 0),120),
                dr.Rep, dr.GroupId) t
        GROUP BY t.GroupId, t.CallTime) s
    GROUP BY s.GroupId) j
WHERE #GroupStatistics.GroupId = j.GroupId

UPDATE #GroupStatistics SET
    AvgSeats = (MaxSeats + RealAvgSeats) / 2

SELECT * FROM #GroupStatistics";

        return [$sql, $bind];
    }

    public function processRow($rec)
    {
        $rec['Duration'] = round($rec['Duration'] / 60);

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

        if (Auth::User()->isType('superadmin')) {
            if (empty($request->dialer)) {
                $this->errors->add('dialer.required', trans('reports.errdialerrequired'));
            } else {
                $this->params['dialer'] = $request->dialer;
            }

            if (empty($request->groups)) {
                $this->errors->add('groups.required', trans('reports.errgroupsrequired'));
            } else {
                $this->params['groups'] = $request->groups;
            }
        } else {
            $this->params['dialer'] = Auth::user()->dialer->reporting_db;
            $this->params['groups'] = [Auth::user()->group_id];
        }

        // Save params to session
        $this->saveSessionParams();

        return $this->errors;
    }
}
