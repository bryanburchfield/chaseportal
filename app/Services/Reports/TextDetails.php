<?php

namespace App\Services\Reports;

use App\Models\Dialer;
use App\Traits\CampaignTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\ReportTraits;
use Illuminate\Support\Carbon;

class TextDetails
{
    use ReportTraits;
    use CampaignTraits;

    private $advanced_table;
    private $extra_cols;

    public function __construct()
    {
        $this->initilaizeParams();

        $this->params['reportName'] = 'reports.text_details';
        $this->params['fromdate'] = '';
        $this->params['todate'] = '';
        $this->params['rep'] = '';
        $this->params['phone'] = '';
        $this->params['columns'] = [
            'Date' => 'reports.date',
            'Campaign' => 'reports.campaign',
            'Subcampaign' => 'reports.subcampaign',
            'Phone' => 'reports.phone',
            'CallerId' => 'reports.callerid',
            'LastName' => 'reports.lastname',
            'FirstName' => 'reports.firstname',
            'Direction' => 'reports.direction',
            'Message' => 'reports.message',
        ];
    }

    public function getFilters()
    {
        $filters = [
            'reps' => $this->getAllReps(false),
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
        // remove tot count
        array_pop($rec);

        $rec['Date'] = Carbon::parse($rec['Date'])->isoFormat('L LT');

        return $rec;
    }

    public function makeQuery($all)
    {
        $this->setHeadings();

        list($fromDate, $toDate) = $this->dateRange($this->params['fromdate'], $this->params['todate']);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $tz = Auth::user()->tz;

        $bind['group_id'] =  Auth::user()->group_id;
        $bind['startdate'] = $startDate;
        $bind['enddate'] = $endDate;
        $bind['rep'] = $this->params['rep'];

        $sql = "SET NOCOUNT ON;";

        $where = '';

        if (!empty($this->params['phone'])) {
            $bind['phone'] = $this->params['phone'];
            $where .= " AND DR.Phone LIKE '1' + :phone + '%'";
        }

        $sql .= " SELECT *, totRows = COUNT(*) OVER()
                FROM (SELECT
                CONVERT(datetimeoffset, DR.CallDate) AT TIME ZONE '$tz' as Date,
                DR.Campaign,
                DR.Subcampaign,
                DR.Phone,
                DR.CallerId,
                L.LastName,
                L.FirstName,
                DR.CallStatus as Direction,
                TR.Record as Message
            FROM [DialingResults] DR WITH(NOLOCK)
            INNER JOIN TextRecordings TR ON TR.RecordId = DR.TextRecordId
            LEFT OUTER JOIN [Leads] L ON L.id = DR.LeadId
            WHERE DR.GroupId = :group_id
            AND DR.CallDate >= :startdate
            AND DR.CallDate <= :enddate
            AND DR.Rep = :rep
            AND DR.CallType = 7
            $where";

        if (session('ssoRelativeCampaigns', 0)) {
            $sql .= " AND DR.Campaign IN (SELECT CampaignName FROM dbo.GetAllRelativeCampaigns(:ssousercamp, 1))";
            $bind['ssousercamp'] = session('ssoUsername');
        }

        if (session('ssoRelativeReps', 0)) {
            $sql .= " AND DR.Rep IN (SELECT RepName FROM dbo.GetAllRelativeReps(:ssouserrep))";
            $bind['ssouserrep'] = session('ssoUsername');
        }

        $sql .= ") tmptable";

        // Check params
        if (!empty($this->params['orderby']) && is_array($this->params['orderby'])) {
            $sort = '';
            foreach ($this->params['orderby'] as $col => $dir) {
                $sort .= ",[$col] $dir";
            }
            $sql .= ' ORDER BY ' . substr($sort, 1);
        } else {
            $sql .= ' ORDER BY Date';
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

        if (empty($request->rep)) {
            $this->errors->add('rep.required', trans('reports.errreprequired'));
        } else {
            $this->params['rep'] = $request->rep;
        }

        if (!empty($request->phone)) {
            $this->params['phone'] = $request->phone;
        }

        // Save params to session
        $this->saveSessionParams();

        return $this->errors;
    }
}
