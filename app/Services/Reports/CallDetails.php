<?php

namespace App\Services\Reports;

use Illuminate\Http\Request;
use App\Services\ReportService;
use Illuminate\Support\MessageBag;


class CallDetails
{
    public $params;

    public function __construct()
    {
        $this->params = [
            'curpage' => 1,
            'pagesize' => 50,
            'totrows' => 0,
            'totpages' => 0,
            'orderby' => [],
            'groupby' => null,
            'hasTotals' => false,
            'fromdate' => '',
            'todate' => '',
            'campaigns' => [],
            'reps' => [],
            'calltype' => '',
            'phone' => '',
            'callerids' => [],
            'callstatuses' => [],
            'durationfrom' => '',
            'durationto' => '',
            'showonlyterm' => 0,
            'columns' => [
                'Rep' => 'Rep',
                'Campaign' => 'Campaign',
                'Phone' => 'Phone',
                'Date' => 'Date',
                'CallStatus' => 'Call Status',
                'Duration' => 'Duration',
                'CallType' => 'Call Type',
                'Details' => 'Call Details',
            ],
        ];
    }

    public function getPageData()
    {
        $data = [
            'jsfile' => [],
            'cssfile' => [],
        ];

        return $data;
    }

    public function getFilters()
    {
        $filters = [
            'campaigns' => ReportService::getAllCampaigns(),
            'inbound_sources' => ReportService::getAllInboundSources(),
            'reps' => ReportService::getAllReps(true),
            'call_statuses' => ReportService::getAllCallStatuses(),
            'call_types' => ReportService::getAllCallTypes(),
        ];

        // Add 'all' to list of call types
        $filters['call_types'] = array_merge(['' => 'All'], $filters['call_types']);

        return $filters;
    }

    public function getResults(Request $request)
    {
        $errors = $this->processInput($request);

        if ($errors->isNotEmpty()) {
            return $errors;
        }

        return [];
    }

    private function processInput(Request $request)
    {
        $errors = new MessageBag();

        if (!empty($request->th_sort)) {
            $col = array_search($request->th_sort, $this->params['columns']);
            $dir = $request->sort_direction ?? 'asc';
            $this->params['orderby'] = [$col => $dir];
        }

        if (!empty($request->curpage)) {
            if ($request->curpage <= 0) {
                $errors->add('pagenumb', "Invalid page number");
            }
            $this->params['curpage'] = $request->curpage;
        }

        if (!empty($request->pagesize)) {
            if ($request->pagesize <= 0) {
                $errors->add('pagesize', "Invalid page size");
            }
            $this->params['pagesize'] = $request->pagesize;
        }

        if (empty($request->fromdate)) {
            $errors->add('fromdate.required', "From date required");
        } else {
            $this->params['fromdate'] = $request->fromdate;
            $from = strtotime($this->params['fromdate']);

            if ($from === false) {
                $errors->add('fromdate.invalid', "From date not a valid date/time");
            }
        }

        if (empty($request->todate)) {
            $errors->add('todate.required', "To date required");
        } else {
            $this->params['todate'] = $request->todate;
            $to = strtotime($this->params['todate']);

            if ($to === false) {
                $errors->add('todate.invalid', "To date not a valid date/time");
            }
        }

        if (!empty($from) && !empty($to) && $to < $from) {
            $errors->add('daterange', "To date must be after From date");
        }

        if (empty($request->campaigns)) {
            $errors->add('campaign.required', "Campaign required");
        } else {
            $this->params['campaigns'] = $request->campaigns;
        }

        if (!empty($request->reps)) {
            $this->params['reps'] = $request->reps;
        }

        if (!empty($request->calltype)) {
            $this->params['calltype'] = $request->calltype;
        }

        if (!empty($request->phone)) {
            $this->params['phone'] = $request->phone;
        }

        if (!empty($request->callerids)) {
            $this->params['callerids'] = $request->callerids;
        }

        if (!empty($request->callstatuses)) {
            $this->params['callstatuses'] = $request->callstatuses;
        }

        if (empty($request->durationfrom)) {
            $this->params['durationfrom'] = '';
            $from = 0;
        } else {
            $this->params['durationfrom'] = $request->durationfrom;
            $from = $request->durationfrom;
        }

        if (empty($request->durationto)) {
            $this->params['durationto'] = '';
            $to = 0;
        } else {
            $this->params['durationto'] = $request->durationto;
            $to = $request->durationto;
        }

        if ($from > $to) {
            $errors->add('duration', "Invalid Duration values");
        }

        if (!empty($request->showonlyterm)) {
            $this->params['showonlyterm'] = $request->showonlyterm;
        }

        return $errors;
    }
}
