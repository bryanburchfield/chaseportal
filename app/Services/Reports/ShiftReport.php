<?php

namespace App\Services\Reports;

use App\Traits\CampaignTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\ReportTraits;
use Illuminate\Support\Carbon;

class ShiftReport
{
    use ReportTraits;
    use CampaignTraits;

    public function __construct()
    {
        $this->initilaizeParams();

        $this->params['reportName'] = 'reports.shift_report';
        $this->params['columns'] = [
            'Date' => 'reports.date',
            'Campaign' => 'reports.campaign',
            'Description' => 'reports.callstatus',
            'TypeName' => 'reports.type',
            'Calls' => 'reports.calls',
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
        ];

        return $filters;
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
                array_pop($rec);
            }
            $this->params['totpages'] = floor($this->params['totrows'] / $this->params['pagesize']);
            $this->params['totpages'] += floor($this->params['totrows'] / $this->params['pagesize']) == ($this->params['totrows'] / $this->params['pagesize']) ? 0 : 1;
        }

        return $this->processResults($results);
    }

    public function makeQuery($all)
    {
        $this->setHeadings();

        list($fromDate, $toDate) = $this->dateRange($this->params['fromdate'], $this->params['todate']);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $tz =  Auth::user()->tz;

        $bind = [];

        $sql = "SET NOCOUNT ON;
        CREATE TABLE #SelectedCampaign(CampaignName varchar(50) Primary Key);";

        // load temp tables
        $join = '';
        if (!empty($this->params['campaigns'])) {
            $campaigns = str_replace("'", "''", implode('!#!', $this->params['campaigns']));
            $bind['campaigns'] = $campaigns;

            $join = 'INNER JOIN #SelectedCampaign C on C.CampaignName = DR.Campaign';
            $sql .= "
            INSERT INTO #SelectedCampaign SELECT DISTINCT [value] from dbo.SPLIT(:campaigns, '!#!');";
        }

        $sql .= "
        CREATE TABLE #ShiftReport(
            Date date,
            Campaign varchar(150),
            CallStatus varchar(50),
            [Description] varchar(255),
            Calls int,
            TypeName varchar(50),
            SortOrder int
        );

        INSERT INTO #ShiftReport(Date, Campaign, CallStatus, [Description], Calls, TypeName, SortOrder)";

        $union = '';
        foreach ($this->params['databases'] as $i => $db) {
            $bind['group_id' . $i] = Auth::user()->group_id;
            $bind['startdate' . $i] = $startDate;
            $bind['enddate' . $i] = $endDate;
            $bind['group_id1' . $i] = Auth::user()->group_id;
            $bind['startdate1' . $i] = $startDate;
            $bind['enddate1' . $i] = $endDate;

            $sql .= " $union SELECT
         CAST(CONVERT(datetimeoffset, dr.Date) AT TIME ZONE '$tz' as date) as Date,
         dr.Campaign,
         dr.CallStatus,
         IsNull(
            (SELECT TOP 1
              CASE
                WHEN [Description] = '' THEN dr.CallStatus
                ELSE [Description]
              END
             FROM [$db].[dbo].[Dispos]
             WHERE Disposition = dr.CallStatus
             AND (GroupId=dr.GroupId OR IsSystem=1)
            ORDER BY GroupID Desc, IsSystem Desc, [Description] Desc), dr.CallStatus) as [Description],
         count(dr.CallStatus) as Calls,
         IsNull(
            (SELECT TOP 1
              dt.TypeName
             FROM [$db].[dbo].[Dispos] d
             INNER JOIN [$db].[dbo].[DispositionTypes] dt ON dt.id = d.Type
             WHERE d.Disposition = dr.CallStatus
             AND (GroupId=dr.GroupId OR IsSystem=1)
            ORDER BY GroupID Desc, IsSystem Desc, [Description] Desc), 'No Connect') as TypeName,
         0 as SortOrder
        FROM [$db].[dbo].[DialingResults] dr WITH(NOLOCK)
        $join
        WHERE dr.GroupId = :group_id$i
        AND IsNull(CallStatus, '') <> ''
        AND CallStatus not in ('CR_CNCT/CON_CAD', 'CR_CNCT/CON_PVD')
        AND dr.Date >= :startdate$i
        AND dr.Date < :enddate$i
        AND CallStatus in (
                    'CR_CNCT/CON_PAMD',
                    'CR_ERROR',
                    'CR_NOANS',
                    'CR_DROPPED',
                    'CR_BUSY',
                    'CR_FAILED',
                    'CR_DISCONNECTED',
                    'CR_UNFINISHED',
                    'CR_NORB',
                    'UNKNOWN',
                    'CR_BAD_NUMBER',
                    'CR_CEPT',
                    'CR_FAXTONE')
        GROUP BY CAST(CONVERT(datetimeoffset, dr.Date) AT TIME ZONE '$tz' as date), dr.Campaign, dr.CallStatus, dr.GroupId
        UNION
        SELECT
         CAST(CONVERT(datetimeoffset, dr.Date) AT TIME ZONE '$tz' as date) as Date,
         dr.Campaign,
         dr.CallStatus,
         IsNull((SELECT TOP 1
                  CASE
                    WHEN [Description] = '' THEN dr.CallStatus
                    ELSE [Description]
                  END
                 FROM [$db].[dbo].[Dispos]
                 WHERE Disposition = dr.CallStatus
                 AND (GroupId=dr.GroupId OR IsSystem=1)
                 ORDER BY GroupID Desc, IsSystem Desc, [Description] Desc), dr.CallStatus) as [Description],
         count(dr.CallStatus) as Calls,
          IsNull(
            (SELECT TOP 1
              dt.TypeName
             FROM [$db].[dbo].[Dispos] d
             INNER JOIN [$db].[dbo].[DispositionTypes] dt ON dt.id = d.Type
             WHERE d.Disposition = dr.CallStatus
             AND (GroupId=dr.GroupId OR IsSystem=1)
            ORDER BY GroupID Desc, IsSystem Desc, [Description] Desc), 'No Connect') as TypeName,
         1 as SortOrder
        FROM [$db].[dbo].[DialingResults] dr WITH(NOLOCK)
        $join
        WHERE dr.GroupId = :group_id1$i
        AND IsNull(CallStatus, '') <> ''
        AND CallStatus not in ('CR_CNCT/CON_CAD', 'CR_CNCT/CON_PVD')
        AND dr.Date >= :startdate1$i
        AND dr.Date < :enddate1$i
        AND CallStatus not in (
                        'CR_CNCT/CON_PAMD',
                        'CR_ERROR',
                        'CR_NOANS',
                        'CR_DROPPED',
                        'CR_BUSY',
                        'CR_FAILED',
                        'CR_DISCONNECTED',
                        'CR_UNFINISHED',
                        'CR_NORB',
                        'UNKNOWN',
                        'CR_BAD_NUMBER',
                        'CR_CEPT',
                        'CR_FAXTONE')
        GROUP BY CAST(CONVERT(datetimeoffset, dr.Date) AT TIME ZONE '$tz' as date), dr.Campaign, dr.CallStatus, dr.GroupId";

            $union = 'UNION ALL';
        }

        $sql .= "
        SELECT
            Date,
            Campaign,
            [Description],
            TypeName,
            SUM(Calls) as Calls,
            totRows = COUNT(*) OVER()
        FROM #ShiftReport
        GROUP BY Date, Campaign, [Description], TypeName, SortOrder";

        // Check params
        if (!empty($this->params['orderby']) && is_array($this->params['orderby'])) {
            $sort = '';
            foreach ($this->params['orderby'] as $col => $dir) {
                $sort .= ",$col $dir";
            }
            $sql .= ' ORDER BY ' . substr($sort, 1);
        } else {
            $sql .= ' ORDER BY Date, Campaign, SortOrder, [Description], Calls desc';
        }

        if (!$all) {
            $offset = ($this->params['curpage'] - 1) * $this->params['pagesize'];
            $sql .= " OFFSET $offset ROWS FETCH NEXT " . $this->params['pagesize'] . " ROWS ONLY";
        }

        return [$sql, $bind];
    }

    private function processResults($results)
    {
        foreach ($results as &$rec) {
            $rec = $this->processRow($rec);
        }
        return $results;
    }

    public function processRow($rec)
    {
        $rec['Date'] = Carbon::parse(($rec['Date']))->isoFormat('L');

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

        if (!empty($request->campaigns)) {
            $this->params['campaigns'] = $request->campaigns;
        }

        // Save params to session
        $this->saveSessionParams();

        return $this->errors;
    }
}
