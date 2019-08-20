<?php

namespace App\Services\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\ReportTraits;

class ShiftReport
{
    use ReportTraits;

    public function __construct()
    {
        $this->initilaizeParams();

        $this->params['reportName'] = 'Shift Report';
        $this->params['fromdate'] = date("m/d/Y 9:00 \A\M");
        $this->params['todate'] = date("m/d/Y 8:00 \P\M");
        $this->params['columns'] = [
            'Date' => 'Date',
            'Campaign' => 'Campaign',
            'Description' => 'Call Status',
            'TypeName' => 'Type',
            'Calls' => 'Total Dials',
        ];
    }

    public function getFilters()
    {
        $filters = [];

        return $filters;
    }

    private function executeReport($all = false)
    {
        // Log::debug($this->params);
        list($fromDate, $toDate) = $this->dateRange($this->params['fromdate'], $this->params['todate']);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $tz =  Auth::user()->tz;
        $bind['group_id1'] =  Auth::user()->group_id;
        $bind['group_id2'] =  Auth::user()->group_id;
        $bind['group_id3'] =  Auth::user()->group_id;
        $bind['group_id4'] =  Auth::user()->group_id;
        $bind['group_id5'] =  Auth::user()->group_id;
        $bind['group_id6'] =  Auth::user()->group_id;
        $bind['startdate1'] = $startDate;
        $bind['enddate1'] = $endDate;
        $bind['startdate2'] = $startDate;
        $bind['enddate2'] = $endDate;

        $sql = "SET NOCOUNT ON;

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
        foreach (Auth::user()->getDatabaseArray() as $db) {
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
             AND (GroupId=:group_id1 OR IsSystem=1)
            ORDER BY GroupID Desc, IsSystem Desc, [Description] Desc), dr.CallStatus) as [Description],
         count(dr.CallStatus) as Calls,
         IsNull(
            (SELECT TOP 1
              dt.TypeName
             FROM [$db].[dbo].[Dispos] d
             INNER JOIN [$db].[dbo].[DispositionTypes] dt ON dt.id = d.Type
             WHERE d.Disposition = dr.CallStatus
             AND (GroupId=:group_id2 OR IsSystem=1)
            ORDER BY GroupID Desc, IsSystem Desc, [Description] Desc), 'No Connect') as TypeName,
         0 as SortOrder
        FROM [$db].[dbo].[DialingResults] dr WITH(NOLOCK)
        WHERE dr.GroupId = :group_id3
        AND IsNull(CallStatus, '') <> ''
        AND CallStatus not in ('CR_CNCT/CON_CAD', 'CR_CNCT/CON_PVD')
        AND dr.Date >= :startdate1
        AND dr.Date < :enddate1
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
        GROUP BY CAST(CONVERT(datetimeoffset, dr.Date) AT TIME ZONE '$tz' as date), dr.Campaign, dr.CallStatus
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
                 AND (GroupId=:group_id4 OR IsSystem=1)
                 ORDER BY GroupID Desc, IsSystem Desc, [Description] Desc), dr.CallStatus) as [Description],
         count(dr.CallStatus) as Calls,
          IsNull(
            (SELECT TOP 1
              dt.TypeName
             FROM [$db].[dbo].[Dispos] d
             INNER JOIN [$db].[dbo].[DispositionTypes] dt ON dt.id = d.Type
             WHERE d.Disposition = dr.CallStatus
             AND (GroupId=:group_id5 OR IsSystem=1)
            ORDER BY GroupID Desc, IsSystem Desc, [Description] Desc), 'No Connect') as TypeName,
         1 as SortOrder
        FROM [$db].[dbo].[DialingResults] dr WITH(NOLOCK)
        WHERE dr.GroupId = :group_id6
        AND IsNull(CallStatus, '') <> ''
        AND CallStatus not in ('CR_CNCT/CON_CAD', 'CR_CNCT/CON_PVD')
        AND dr.Date >= :startdate2
        AND dr.Date < :enddate2
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
        GROUP BY CAST(CONVERT(datetimeoffset, dr.Date) AT TIME ZONE '$tz' as date), dr.Campaign, dr.CallStatus";

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
            }
            $this->params['totpages'] = floor($this->params['totrows'] / $this->params['pagesize']);
            $this->params['totpages'] += floor($this->params['totrows'] / $this->params['pagesize']) == ($this->params['totrows'] / $this->params['pagesize']) ? 0 : 1;
        }

        return $this->processResults($results);
    }

    private function processResults($results)
    {
        foreach ($results as &$rec) {
            $rec['Date'] = (new \DateTime($rec['Date']))->format('m/d/Y');
        }
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

        // Save params to session
        $this->saveSessionParams();

        return $this->errors;
    }
}
