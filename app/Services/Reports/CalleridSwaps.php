<?php

namespace App\Services\Reports;

use App\Models\PhoneFlag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\ReportTraits;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CalleridSwaps
{
    use ReportTraits;

    public function __construct()
    {
        $this->initilaizeParams();

        $this->params['reportName'] = 'reports.callerid_swaps';
        $this->params['fromdate'] = '';
        $this->params['todate'] = '';
        $this->params['flag_type'] = '';
        $this->params['phone'] = '';
        $this->params['columns'] = [
            'Date' => 'reports.date',
            'phone' => 'reports.phone',
            'ring_group' => 'reports.description',
            'owned' => 'reports.owned',
            'calls' => 'reports.calls',
            'connect_ratio' => 'reports.connectpct',
            'flagged' => 'reports.flagged',
            'replaced_by' => 'reports.replaced_by',
        ];
    }

    public function getFilters()
    {
        $filters = [
            'db_list' => Auth::user()->getDatabaseArray(),
            'flag_type' => [
                '' =>  trans('general.all'),
                'flagged' => trans('reports.only_flagged'),
                'clean' => trans('reports.only_clean'),
            ],
        ];

        return $filters;
    }

    public function getInfo()
    {
        return [
            'columns' => $this->params['columns'],
            'paragraphs' => 3,
        ];
    }

    private function executeReport($all = false)
    {
        list($sql, $bind) = $this->makeQuery($all);

        $results = $sql->get()->toArray();

        if (empty($results)) {
            $this->params['totrows'] = 0;
            $this->params['totpages'] = 1;
            $this->params['curpage'] = 1;
        } else {
            $this->params['totrows'] = $results[0]['totrows'];

            $clean_count = $results[0]['clean_count'];
            $flagged_count = $results[0]['flagged_count'];

            foreach ($results as &$rec) {
                $rec = $this->processRow($rec);
            }
            $results[] = [
                'Date' => 'Clean: ' . number_format($clean_count),
                'phone' => 'Flagged: ' . number_format($flagged_count),
                'ring_group' => 'Total: ' .  number_format($this->params['totrows']),
                'owned' => '',
                'calls' => '',
                'connect_ratio' => '',
                'flagged' => '',
                'replaced_by' => '',
            ];

            $this->params['totpages'] = floor($this->params['totrows'] / $this->params['pagesize']);
            $this->params['totpages'] += floor($this->params['totrows'] / $this->params['pagesize']) == ($this->params['totrows'] / $this->params['pagesize']) ? 0 : 1;
        }

        return $results;
    }

    public function processRow($rec)
    {
        // remove tots
        array_pop($rec);
        array_pop($rec);
        array_pop($rec);

        $rec['Date'] = Carbon::parse($rec['Date'])->isoFormat('L LT');

        // Strip leading 1
        $rec['phone'] = substr($rec['phone'], 1);
        $rec['replaced_by'] = substr($rec['replaced_by'], 1);

        return $rec;
    }

    public function makeQuery($all)
    {
        $this->setHeadings();

        list($fromDate, $toDate) = $this->dateRange($this->params['fromdate'], $this->params['todate']);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $query = PhoneFlag::where('group_id', Auth::user()->group_id)
            ->where('run_date', '>=', $startDate)
            ->where('run_date', '<=', $endDate);

        if ($this->params['flag_type'] == 'flagged') {
            $query->where('flagged', 1);
        }

        if ($this->params['flag_type'] == 'clean') {
            $query->where('flagged', 0);
        }

        if (!empty($this->params['phone'])) {
            $query->where('phone', '1' . $this->params['phone']);
        }

        $clean_count = (clone ($query))->where('flagged', 0)->count();
        $flagged_count = (clone ($query))->where('flagged', 1)->count();
        $count = $query->count();

        $query->select([
            'run_date AS Date',
            'phone',
            'ring_group',
            'owned',
            'calls',
            'connect_ratio',
            'flagged',
            'replaced_by',
            DB::raw($clean_count . ' AS clean_count'),
            DB::raw($flagged_count . ' AS flagged_count'),
            DB::raw($count . ' AS totrows')
        ]);

        // Check params
        if (!empty($this->params['orderby']) && is_array($this->params['orderby'])) {
            foreach ($this->params['orderby'] as $col => $dir) {
                $query->orderBy($col, $dir);
            }
        } else {
            $query->orderBy('run_date')->orderBy('phone');
        }

        if (!$all) {
            $query->skip(($this->params['curpage'] - 1) * $this->params['pagesize'])
                ->take($this->params['pagesize']);
        }

        return [$query, null];
    }

    private function processInput(Request $request)
    {
        // Get vals from session if not set (for exports)
        $request = $this->getSessionParams($request);

        // Check page filters
        $this->checkPageFilters($request);

        // Check report filters
        $this->checkDateRangeFilters($request);

        if (!empty($request->flag_type)) {
            $this->params['flag_type'] = $request->flag_type;
        }

        if (!empty($request->phone)) {
            $this->params['phone'] = $request->phone;
        }

        // Save params to session
        $this->saveSessionParams();

        return $this->errors;
    }
}
