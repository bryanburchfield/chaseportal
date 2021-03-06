<?php

namespace App\Services\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\ReportTraits;

class MissedCalls
{
    use ReportTraits;

    public function __construct()
    {
        $this->initilaizeParams();

        $this->params['reportName'] = 'reports.missed_calls';
        $this->params['columns'] = [
            'Phone' => 'reports.phone',
            'MissedCalls' => 'reports.missedcalls',
            'FirstName' => 'reports.firstname',
            'LastName' => 'reports.lastname',
            'Date' => 'reports.mostrecent',
            'CallStatus' => 'reports.callstatus',
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
            $this->params['totrows'] = $results[0]['totRows'];

            foreach ($results as &$rec) {
                $rec = $this->processRow($rec);
            }
            $this->params['totpages'] = floor($this->params['totrows'] / $this->params['pagesize']);
            $this->params['totpages'] += floor($this->params['totrows'] / $this->params['pagesize']) == ($this->params['totrows'] / $this->params['pagesize']) ? 0 : 1;
        }

        return $results;
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

        SELECT * INTO #MissedPhones FROM (";

        $union = '';
        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id' . $i] = Auth::user()->group_id;
            $bind['startdate' . $i] = $startDate;
            $bind['enddate' . $i] = $endDate;

            $sql .= " $union SELECT DR.Phone, Max(DR.Date) as MaxDate, COUNT(DR.Phone) as MissedCalls,
            LD.FirstName, LD.LastName
            FROM [$db].[dbo].[DialingResults] DR
            CROSS APPLY (
                SELECT TOP 1 FirstName, LastName
                FROM [$db].[dbo].[Leads]
                WHERE PrimaryPhone = SUBSTRING(DR.Phone, 2, LEN(DR.Phone))
                AND GroupId = DR.GroupId
                AND (FirstName != '' OR LastName != '')
                ORDER BY LastUpdated DESC
            ) LD
            WHERE DR.CallType IN (1,11)
            AND DR.CallStatus IN ('CR_HANGUP', 'Inbound Voicemail')
            AND Duration > 0
            AND DR.Date >= :startdate$i
            AND DR.Date < :enddate$i
            AND DR.GroupId = :group_id$i";

            if (session('ssoRelativeCampaigns', 0)) {
                $sql .= " AND DR.Campaign IN (SELECT CampaignName FROM dbo.GetAllRelativeCampaigns(:ssousercamp$i, 1))";
                $bind['ssousercamp' . $i] = session('ssoUsername');
            }

            if (session('ssoRelativeReps', 0)) {
                $sql .= " AND DR.Rep IN (SELECT RepName FROM dbo.GetAllRelativeReps(:ssouserrep$i))";
                $bind['ssouserrep' . $i] = session('ssoUsername');
            }

            $sql .= "
            GROUP BY DR.Phone, LD.FirstName, LD.LastName";

            $union = 'UNION';
        }

        $sql .= ") tmp;

        SELECT *, totRows = COUNT(*) OVER() FROM (";

        $union = '';
        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id1' . $i] = Auth::user()->group_id;

            $sql .= " $union SELECT
                MP.Phone,
                MP.MissedCalls,
                MP.FirstName,
                MP.LastName,
                DR.Date,
                DR.CallStatus
            FROM #MissedPhones MP
            CROSS APPLY (
                SELECT TOP 1 Date, CallStatus
                FROM [$db].[dbo].[DialingResults]
                WHERE Phone = MP.Phone
                AND GroupId = :group_id1$i
                AND Date >= MP.MaxDate
                ORDER BY Date DESC
            ) DR";

            $union = 'UNION';
        }

        $sql .= ") tmp";

        // Check params
        if (!empty($this->params['orderby']) && is_array($this->params['orderby'])) {
            $sort = '';
            foreach ($this->params['orderby'] as $col => $dir) {
                $sort .= ",$col $dir";
            }
            $sql .= ' ORDER BY ' . substr($sort, 1);
        } else {
            $sql .= ' ORDER BY Phone';
        }

        if (!$all) {
            $offset = ($this->params['curpage'] - 1) * $this->params['pagesize'];
            $sql .= " OFFSET $offset ROWS FETCH NEXT " . $this->params['pagesize'] . " ROWS ONLY";
        }

        return [$sql, $bind];
    }

    public function processRow($rec)
    {
        array_pop($rec);
        $rec['Date'] = $this->utcToLocal($rec['Date'], Auth::user()->iana_tz)->isoFormat('L LT');

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

        // Save params to session
        $this->saveSessionParams();

        return $this->errors;
    }
}
