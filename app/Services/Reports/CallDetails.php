<?php

namespace App\Services\Reports;

use Illuminate\Http\Request;
use App\Services\ReportService;

class CallDetails
{
    public $params;

    public function __construct()
    {
        //
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
            'reps' => ReportService::getAllReps(),
            'call_statuses' => ReportService::getAllCallStatuses(),
            'call_types' => ReportService::getAllCallTypes(),
        ];

        // Add 'all' to list of call types
        $filters['call_types'] = array_merge(['' => 'All'], $filters['call_types']);

        return $filters;
    }

    public function getResults(Request $request)
    {
        return [];
    }
}
