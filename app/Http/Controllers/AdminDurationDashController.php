<?php

namespace App\Http\Controllers;

use App\Traits\DashTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminDurationDashController extends Controller
{
    use DashTraits;

    public function index(Request $request)
    {
        $this->getSession($request);

        $campaigns = $this->campaignGroups();

        $jsfile[] = "admindurationdash.js";
        $cssfile[] = "admindurationdash.css";

        $data = [
            'isApi' => $this->isApi,
            'campaign' => $this->campaign,
            'dateFilter' => $this->dateFilter,
            'inorout' => $this->inorout,
            'campaign_list' => $campaigns,
            'curdash' => 'admindurationdash',
            'jsfile' => $jsfile,
            'cssfile' => $cssfile,
        ];

        return view('admindurationdash')->with($data);
    }

    public function callVolume(Request $request)
    {
        $this->getSession($request);

        $details = $this->filterDetails();

        $results = $this->getCallVolume();

        $campaign_table = [];

        foreach ($results as $rec) {
            if (!isset($campaign_table[$rec['Campaign']])) {
                $campaign_table[$rec['Campaign']]['Count'] = 0;
                $campaign_table[$rec['Campaign']]['Minutes'] = 0;
            }
            $campaign_table[$rec['Campaign']]['Count'] += $rec['cnt'];
            $campaign_table[$rec['Campaign']]['Minutes'] += $rec['secs'];
        }

        unset($rec);

        foreach ($campaign_table as &$rec) {
            $rec['Minutes'] = number_format($rec['Minutes'] / 60, 2);
        }

        return ['call_volume' => [
            'campaign_table' => $campaign_table,
            'details' => $details,
        ]];
    }

    private function getCallVolume()
    {

        // and CallStatus like 'CR[_]%'   almost but not quite

        $campaign = $this->campaign;
        $dateFilter = $this->dateFilter;

        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $sql = "SELECT Campaign, COUNT(*) cnt, SUM(Duration) secs
            FROM DialingResults DR
            WHERE GroupId = :groupid
            AND Date >= :fromdate
            AND Date < :todate
            AND CallType NOT IN (7,8)
            AND CallStatus NOT IN ('Inbound', 'CR_CNCT/CON_CAD', 'CR_CNCT/CON_PVD')
            GROUP BY Campaign
            ORDER BY Campaign";

        $bind = [
            'groupid' => Auth::user()->group_id,
            'fromdate' => $startDate,
            'todate' => $endDate,
        ];

        list($where, $extrabind) = $this->campaignClause('DR', 0, $campaign);
        $sql .= " $where";
        $bind = array_merge($bind, $extrabind);

        return $this->runSql($sql, $bind);
    }
}
