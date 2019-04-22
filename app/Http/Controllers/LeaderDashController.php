<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Campaign;
use App\DialingResult;
use App\AgentActivity;

class LeaderDashController extends Controller
{
    use DashTraits;

    public function index(Request $request)
    {
        $this->getSession();

        $db = Auth::user()->db;
        config(['database.connections.sqlsrv.database' => $db]);

        $groupId = Auth::user()->group_id;
        $campaigns = Campaign::where('GroupId', $groupId)->where('IsActive', 1)->pluck('CampaignName')->toArray();
        natcasesort($campaigns);
        array_unshift($campaigns, 'Total');

        $data = [
            'isApi' => $this->isApi,
            'campaign' => $this->campaign,
            'datefilter' => $this->dateFilter,
            'inorout' => $this->inorout,
            'campaign_list' => $campaigns,
            'curdash' => 'leaderdash',
        ];
        return view('leaderdash')->with($data);
    }

    public function callVolume(Request $request)
    {
        $this->getSession();

        $campaign = $this->campaign;
        $dateFilter = $this->dateFilter;
        $timeZoneName = Auth::user()->tz;

        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $byHour = ($dateFilter == 'today' || $dateFilter == 'yesterday') ? true : false;

        // group by date/hour or just date
        if ($byHour) {
            $mapFunction = 'dateTimeToHour';
            $format = 'Y-m-d H:i:s.000';
            $modifier = "+1 hour";
            $xAxis = "DATEADD(HOUR, DATEPART(HOUR, CONVERT(datetimeoffset, Date) AT TIME ZONE '$timeZoneName'),
            CAST(CAST(CONVERT(datetimeoffset, Date) AT TIME ZONE '$timeZoneName' AS DATE) AS DATETIME))";
        } else {
            $mapFunction = 'dateTimeToDay';
            $format = 'Y-m-d 00:00:00.000';
            $modifier = "+1 day";
            $xAxis = "CAST(CAST(CONVERT(datetimeoffset, Date) AT TIME ZONE '$timeZoneName' AS DATE) AS DATETIME)";
        }

        $select = "'Time' = $xAxis,
        'Outbound' = SUM(CASE WHEN CallType IN ('0','4','5','6','10','12','14','15') THEN 1 ELSE 0 END),
        'Inbound' = SUM(CASE WHEN CallType IN ('1','11') THEN 1 ELSE 0 END),
        'Manual' = SUM(CASE WHEN CallType IN ('2') THEN 1 ELSE 0 END),
        'Duration' = SUM(CASE WHEN CallType IN ('1','11') THEN HandleTime ELSE 0 END)";

        $query = DialingResult::select(DB::raw($select));

        $query->where('CallType', '!=', 7)
            ->where('Duration', '>', 0)
            ->whereNotIn('CallStatus', ['CR_CNCT/CON_CAD', 'CR_CNCT/CON_PVD', 'Inbound', 'TRANSFERRED', 'PARKED', 'SMS Received', 'SMS Delivered'])
            ->where('GroupId', Auth::user()->group_id)
            ->where('Date', '>=', $startDate)
            ->where('Date', '<', $endDate);

        if (!empty($campaign) && $campaign != 'Total') {
            $query->where('Campaign', $campaign);
        }

        $query->groupBy(DB::raw($xAxis));

        $result = $query->get()->toArray();

        // split the results into three arrays
        $params = [
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'modifier' => $modifier,
            'byHour' => $byHour,
            'format' => $format,
            'zeroRec' => [
                'Time' => '',
                'Outbound' => 0,
                'Inbound' => 0,
                'Manual' => 0,
                'Duration' => 0,
            ],
        ];
        $result = $this->formatVolume($result, $params);

        // format the xAxis datetimes
        $result = array_map(array(&$this, $mapFunction), $result);

        // prepare json return
        $time_labels = [];
        $inbound = [];
        $outbound = [];
        $manual = [];
        $tot_inbound = 0;
        $tot_outbound = 0;

        foreach ($result as $r) {

            $tot_inbound += $r['Inbound'];
            $tot_outbound += $r['Outbound'];
            $tot_outbound += $r['Manual'];

            if (!strpos($r['Time'], ':')) {
                $datetime = date("n/j/y", strtotime($r['Time']));
            } else {
                $datetime = $r['Time'];
            }

            array_push($time_labels, $datetime);
            array_push($inbound, $r['Inbound']);
            array_push($outbound, $r['Outbound']);
            array_push($manual, $r['Manual']);
        }

        $new_result['time_labels'] = $time_labels;
        $new_result['inbound'] = $inbound;
        $new_result['outbound'] = $outbound;
        $new_result['manual'] = $manual;
        $new_result['tot_outbound'] = $tot_outbound;
        $new_result['tot_inbound'] = $tot_inbound;

        $details = $this->filterDetails();
        $new_result['details'] = $details;

        $return['call_volume'] = $new_result;
        echo json_encode($return);
    }

    public function callsByCampaign(Request $request)
    {
        $this->getSession();

        $campaign = $this->campaign;
        $dateFilter = $this->dateFilter;

        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $select = "DialingResults.Campaign, 'Rep Call Count' = COUNT(DialingResults.CallStatus)";

        $query = DialingResult::select(DB::raw($select));

        $query->join('Dispos as D', function ($join) {
            $join->on('D.id', '=', 'DialingResults.DispositionId')
                ->where('D.IsSystem', 0);
        });

        $query->where('DialingResults.Rep', '!=', '')
            ->where('DialingResults.GroupId', Auth::user()->group_id)
            ->where('DialingResults.Date', '>=', $startDate)
            ->where('DialingResults.Date', '<', $endDate)
            ->groupBy('DialingResults.Campaign');

        if (!empty($campaign) && $campaign != 'Total') {
            $query->where('DialingResults.Campaign', $campaign);
        }

        $result1 = $query->get()->toArray();

        // now get calls that didn't go to reps
        $select = "Campaign, 'NonRep Call Count' = COUNT(CallStatus)";

        $query = DialingResult::select(DB::raw($select));

        $query->where('Rep', '')
            ->whereNotIn('CallStatus', ['CR_CNCT/CON_CAD', 'CR_CNCT/CON_PVD', 'Inbound', 'TRANSFERRED', 'PARKED'])
            ->where('GroupId', Auth::user()->group_id)
            ->where('Date', '>=', $startDate)
            ->where('Date', '<', $endDate)
            ->groupBy('Campaign');

        if (!empty($campaign) && $campaign != 'Total') {
            $query->where('Campaign', $campaign);
        }

        $result2 = $query->get()->toArray();

        // process to json
        $call_campaigns = [];
        $rep_call_count = [];
        $agent_call_campaigns = [];
        $call_count = [];

        foreach ($result1 as $r) {
            array_push($call_campaigns, $r['Campaign']);
            array_push($rep_call_count, $r['Rep Call Count']);
        }

        foreach ($result2 as $r) {
            array_push($agent_call_campaigns, $r['Campaign']);
            array_push($call_count, $r['NonRep Call Count']);
        }

        $new_result['call_campaigns'] = $call_campaigns;
        $new_result['rep_call_count'] = $rep_call_count;
        $new_result['agent_call_campaigns'] = $agent_call_campaigns;
        $new_result['call_count'] = $call_count;

        $return['calls_by_campaign'] = $new_result;
        echo json_encode($return);
    }

    public function leaderBoard(Request $request)
    {
        $this->getSession();

        $campaign = $this->campaign;
        $dateFilter = $this->dateFilter;

        list($fromDate, $toDate) = $this->dateRange($dateFilter);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $select = "DialingResults.Rep,
                'Call Count' = COUNT(DialingResults.CallStatus),
                'Talk Secs' = SUM(DialingResults.Duration),
                'Sales' = COUNT(CASE WHEN D.Type = '3' THEN 1 ELSE NULL END)";

        $query = DialingResult::select(DB::raw($select));

        $query->join('Dispos as D', function ($join) {
            $join->on('D.id', '=', 'DialingResults.DispositionId')
                ->where('D.IsSystem', 0);
        });

        $query->where('DialingResults.Rep', '!=', '')
            ->where('DialingResults.GroupId', Auth::user()->group_id)
            ->where('DialingResults.Date', '>=', $startDate)
            ->where('DialingResults.Date', '<', $endDate)
            ->groupBy('Rep')
            ->orderBy('Sales', 'desc')
            ->orderBy('Rep', 'asc')
            ->take(10);

        $result = $query->get()->toArray();

        // process to json
        foreach ($result as &$value) {
            $value['Talk Secs'] = gmdate("H:i:s", $value['Talk Secs']);
        }

        $return['leader_board'] = $result;
        echo json_encode($return);
    }
}
