<?php

namespace App\Services\Reports;

use App\Models\AreaCode;
use App\Traits\CampaignTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\ReportTraits;

class LeadNpa
{
    use ReportTraits;
    use CampaignTraits;

    public function __construct()
    {
        $this->initilaizeParams();

        $this->params['reportName'] = 'reports.agent_activity';
        $this->params['nostreaming'] = 1;
        $this->params['campaigns'] = [];
        $this->params['subcampaigns'] = [];
        $this->params['columns'] = [
            'State' => 'reports.state',
            'Npa' => 'reports.npa',
            'City' => 'reports.city',
            'Timezone' => 'reports.timezone',
            'Calls' => 'reports.calls',
            'Pct' => 'reports.pct',
        ];
    }

    public function getFilters()
    {
        $filters = [
            'campaigns' => $this->getAllCampaigns(
                $this->params['fromdate'],
                $this->params['todate']
            ),
            'subcampaigns' => [],
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

        // convert to collection
        $results = collect($results);

        if ($results->isEmpty()) {
            $this->params['totrows'] = 0;
            $this->params['totpages'] = 1;
            $this->params['curpage'] = 1;
        } else {
            $this->params['totrows'] = $results->count();
            $this->params['totpages'] = floor($this->params['totrows'] / $this->params['pagesize']);
            $this->params['totpages'] += floor($this->params['totrows'] / $this->params['pagesize']) == ($this->params['totrows'] / $this->params['pagesize']) ? 0 : 1;

            $total_calls = $results->sum('Cnt');

            // get state and calc pct
            $results->transform(function ($item, $key) use ($total_calls) {
                $area_code = AreaCode::find($item['Npa']);

                if (!$area_code) {
                    $area_code = new AreaCode();
                }

                return [
                    'State' => $area_code->state,
                    'Npa' => $item['Npa'],
                    'City' => $area_code->city,
                    'Timezone' => $area_code->timezone,
                    'Calls' => (int) $item['Cnt'],
                    'Pct' => number_format($item['Cnt'] / $total_calls * 100, 2) . '%',
                ];
            });

            // check sorting
            if (!empty($this->params['orderby']) && is_array($this->params['orderby'])) {
                foreach ($this->params['orderby'] as $col => $dir) {
                    if ($dir == 'desc') {
                        $results = $results->sortByDesc($col);
                    } else {
                        $results = $results->sortBy($col);
                    }
                }
            } else {
                // default sort by state, npa
                $results = $results->sortBy('State')->sortBy('Npa');
            }

            // extract current page
            if (!$all) {
                $results = $results->forPage($this->params['curpage'], $this->params['pagesize']);
            }
        }

        // Convert back to array
        return $results->toArray();
    }

    public function processRow($rec)
    {
        return $rec;
    }

    public function makeQuery($all)
    {
        $this->setHeadings();

        list($fromDate, $toDate) = $this->dateRange($this->params['fromdate'], $this->params['todate']);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $campaigns = str_replace("'", "''", implode('!#!', $this->params['campaigns']));
        $subcampaigns = str_replace("'", "''", implode('!#!', $this->params['subcampaigns']));

        $bind['group_id'] = Auth::user()->group_id;
        $bind['startdate'] = $startDate;
        $bind['enddate'] = $endDate;

        $sql = "SET NOCOUNT ON;

    SELECT 
        SUBSTRING(Phone,2,3) as Npa,
        COUNT(*) as Cnt
    FROM DialingResults WITH(NOLOCK)
    WHERE GroupId = :group_id
    AND	CallDate >= :startdate
    AND CallDate < :enddate
    AND CallType = 0
    AND Phone LIKE '1__________' 
    AND CallStatus NOT IN ('CR_CNCT/CON_CAD', 'CR_CNCT/CON_PVD', 'CR_BAD_NUMBER', 'CR_UNFINISHED')";

        if (!empty($campaigns)) {
            $bind['campaigns'] = $campaigns;
            $sql .= " AND Campaign in (SELECT value FROM dbo.SPLIT(:campaigns, '!#!'))";
        }

        if (!empty($subcampaigns)) {
            $bind['subcampaigns'] = $subcampaigns;
            $sql .= " AND Subcampaign in (SELECT value FROM dbo.SPLIT(:subcampaigns, '!#!'))";
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
    GROUP BY SUBSTRING(Phone,2,3)";

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

        if (!empty($request->campaigns)) {
            $this->params['campaigns'] = $request->campaigns;
        }

        if (!empty($request->subcampaigns)) {
            $this->params['subcampaigns'] = $request->subcampaigns;
        }

        // Save params to session
        $this->saveSessionParams();

        return $this->errors;
    }
}
