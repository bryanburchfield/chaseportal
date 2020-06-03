<?php

namespace App\Http\Controllers;

use App\Models\PauseCode;
use App\Traits\DashTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ComplianceDashController extends Controller
{
    use DashTraits;

    public function settingsIndex()
    {
        $page = [
            'menuitem' => 'compliancedash',
            'type' => 'page',
        ];

        $data = [
            'page' => $page,
            'pause_codes' => $this->getPauseCodes(),
        ];

        return view('compliancedash_settings')->with($data);
    }

    public function getPauseCodes()
    {
        // get pause codes from our settings table
        $pause_codes = PauseCode::where('group_id', Auth::User()->group_id)->get();

        // get pause codes from sql server
        $sql = "SELECT DISTINCT Disposition
            FROM Dispos
            WHERE Location = '3'
            AND (IsSystem = 1 OR GroupId = :groupid)";

        $bind['groupid'] = Auth::User()->group_id;

        $results = resultsToList($this->runSql($sql, $bind));

        foreach ($results as $disposition) {
            if ($pause_codes->where('code', $disposition)->isEmpty()) {
                $pause_codes->push(new PauseCode([
                    'group_id' => Auth::User()->group_id,
                    'user_id' => Auth::User()->id,
                    'code' => $disposition,
                    'minutes_per_day' => 0,
                    'times_per_day' => 0,
                ]));
            }
        }

        return $pause_codes->sortBy('code');
    }

    public function updateSettings(Request $request)
    {
        // Bail if they canceled
        if ($request->has('cancel')) {
            return redirect()->action('MasterDashController@complianceDashboard');
        }

        // check that they sumbitted something
        if ($request->missing('code')) {
            return redirect()->action('MasterDashController@complianceDashboard');
        }

        // update/insert records
        foreach ($request->code as $i => $code) {

            $pause_code = PauseCode::firstOrNew([
                'group_id' => Auth::User()->group_id,
                'code' => $code,
            ]);

            $pause_code->user_id = Auth::User()->id;
            $pause_code->minutes_per_day = $request->minutes_per_day[$i];
            $pause_code->times_per_day = $request->times_per_day[$i];
            $pause_code->save();
        }

        session()->flash('flash', trans('general.settings_saved'));
        return redirect()->action('MasterDashController@complianceDashboard');
    }

    public function agentCompliance(Request $request)
    {
        $this->getSession($request);

        $details = $this->filterDetails();

        $agent_compliance = $this->getAgentCompliance();

        return ['agent_compliance' => [
            'agent_compliance' => $agent_compliance,
            'details' => $details,
        ]];
    }

    public function agentDetail(Request $request)
    {
        $this->getSession($request);

        $agent_detail = $this->getAgentDetail($request->rep);

        return ['agent_detail' => [
            'agent_detail' => $agent_detail,
        ]];
    }

    private function getAgentCompliance()
    {
        list($fromDate, $toDate) = $this->dateRange($this->dateFilter);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $sql = 'SET NOCOUNT ON;';

        $bind['group_id'] =  Auth::user()->group_id;
        $bind['startdate'] = $startDate;
        $bind['enddate'] = $endDate;

        $sql .= "SELECT Rep,
              SUM(CASE WHEN AA.Action != 'Paused' THEN Duration ELSE 0 END) as WorkedTime,
              SUM(CASE WHEN AA.Action = 'Paused' THEN Duration ELSE 0 END) as PausedTime
            FROM [AgentActivity] AA WITH(NOLOCK)";

        $sql .= "
            WHERE AA.GroupId = :group_id
            AND AA.Date >= :startdate
            AND AA.Date < :enddate
            AND AA.Action NOT IN ('Login', 'Logout')";

        list($where, $extrabind) = $this->campaignClause('AA', 0, $this->campaign);
        $sql .= " $where";
        $bind = array_merge($bind, $extrabind);

        $sql .= " GROUP BY Rep ORDER BY Rep";

        // Pause details (select for all camps)
        $bind['group_id2'] =  Auth::user()->group_id;
        $bind['startdate2'] = $startDate;
        $bind['enddate2'] = $endDate;

        $sql .= " SELECT Rep, Date, Campaign, Duration, Details
            FROM [AgentActivity] AA WITH(NOLOCK)";

        $sql .= "
            WHERE AA.GroupId = :group_id2
            AND AA.Date >= :startdate2
            AND AA.Date < :enddate2
            AND AA.Action = 'Paused'
            ORDER BY Rep, Date";

        list($rep_array, $pause_array) = $this->runMultiSql($sql, $bind);

        $results = $this->processResults($rep_array, $pause_array);

        return $results;
    }

    private function getAgentDetail($rep)
    {
        list($fromDate, $toDate) = $this->dateRange($this->dateFilter);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $sql = 'SET NOCOUNT ON;';

        $bind['group_id'] =  Auth::user()->group_id;
        $bind['startdate'] = $startDate;
        $bind['enddate'] = $endDate;
        $bind['rep'] = $rep;

        $sql .= " SELECT AA.id, AA.Rep, AA.Date, AA.Campaign, [Action], AA.Duration, AA.Details
            FROM [AgentActivity] AA WITH(NOLOCK)";

        $sql .= "
            WHERE AA.GroupId = :group_id
            AND AA.Date >= :startdate
            AND AA.Date < :enddate
            AND AA.Rep = :rep
            ORDER BY Rep, Date";

        $details = $this->processDetails($sql, $bind);

        return $details;
    }

    private function processResults($rep_array, $pause_array)
    {
        $rep_details = collect();

        foreach ($rep_array as $rep_rec) {

            // get pause recs for this Rep
            $keys = array_keys(array_column($pause_array, 'Rep'), $rep_rec['Rep']);
            $rep_pause_array = array_map(function ($key) use ($pause_array) {
                return $pause_array[$key];
            }, $keys);

            // Calculate paused time
            $allowed_pause_time = $this->calcAllowedPausedTime($rep_pause_array, $rep_details);
            $tot_worked_time = $rep_rec['WorkedTime'] + $allowed_pause_time;
            if (($rep_rec['WorkedTime'] + $rep_rec['PausedTime']) == 0) {
                $pct_worked = 0;
            } else {
                $pct_worked = $tot_worked_time / ($rep_rec['WorkedTime'] + $rep_rec['PausedTime']) * 100;
            }

            $results[] = [
                'Rep' => $rep_rec['Rep'],
                'WorkedTime' => $this->secondsToHms($rep_rec['WorkedTime']),
                'PausedTime' => $this->secondsToHms($rep_rec['PausedTime']),
                'AllowedPausedTime' => $this->secondsToHms($allowed_pause_time),
                'TotWorkedTime' => $this->secondsToHms($tot_worked_time),
                'PctWorked' => number_format($pct_worked, 2) . '%',
                'PctWorkedInteger' => round($pct_worked),
                'detail_link' => action('ComplianceDashController@agentDetail', ['rep' => $rep_rec['Rep']]),
            ];
        }

        return $results;
    }

    private function processDetails($sql, $bind)
    {
        $rep_details = collect();
        $pause_recs = [];

        foreach ($this->yieldSql($sql, $bind) as $rec) {

            switch ($rec['Action']) {
                case 'Login':
                    if ($this->checkCampaign($rec['Campaign'])) {
                        $rep_details->push($this->detailRec($rec));
                    }
                    break;
                case 'Logout':
                    if ($this->checkCampaign($rec['Campaign'])) {
                        $rep_details->push($this->detailRec($rec));
                    }
                    break;
                case 'Paused':
                    if (round($rec['Duration']) > 0) {
                        $rep_details->push($this->detailRec($rec));
                        $pause_recs[] = [
                            'id' => $rec['id'],
                            'Date' => $rec['Date'],
                            'Campaign' => $rec['Campaign'],
                            'Duration' => $rec['Duration'],
                            'Details' => $rec['Details'],
                        ];
                    }
                    break;
                default:
                    if ($this->checkCampaign($rec['Campaign'])) {
                        if (round($rec['Duration']) > 0) {
                            $rep_details->push($this->detailRec($rec));
                        }
                    }
            }
        }

        $allowed_pause_time = $this->calcAllowedPausedTime($pause_recs, $rep_details);

        return $rep_details;
    }

    private function detailRec($rec)
    {
        $detail = [
            'id' => $rec['id'],
            'Date' =>  $this->utcToLocal($rec['Date'])->toDateTimeString(),
            'Action' => $rec['Action'],
            'Details' => '',
            'WorkedTime' => '',
            'PausedTime' => '',
            'AllowedPausedTime' => '',
        ];

        switch ($rec['Action']) {
            case 'Login':
                $detail['Details'] = $rec['Campaign'];
                break;
            case 'Logout':
                $detail['Details'] = $rec['Campaign'];
                break;
            case 'Paused':
                if (round($rec['Duration']) > 0) {
                    $detail['Details'] = $rec['Details'];
                    $detail['PausedTime'] = $this->secondsToHms($rec['Duration']);
                }
                break;
            default:
                if (round($rec['Duration']) > 0) {
                    $detail['WorkedTime'] = $this->secondsToHms($rec['Duration']);
                }
        }

        return $detail;
    }

    private function calcAllowedPausedTime(array $pause_recs, Collection $rep_details)
    {
        $allowed_pause_time = 0;

        $pause_codes = PauseCode::where('group_id', Auth::User()->group_id)
            ->select(['code', 'minutes_per_day', 'times_per_day'])
            ->get();

        $day = '';
        foreach ($pause_recs as $rec) {
            // if no duration then ignore
            if ($rec['Duration'] == 0) {
                continue;
            }

            // Convert to local
            $rec_day = $this->utcToLocal($rec['Date'])->toDateString();

            // reset counts if day changed
            if ($rec_day != $day) {
                $pause_codes->map(function ($item) {
                    $item['day_count'] = 0;
                    $item['day_duration'] = 0;

                    // set unlimited if one value is zero
                    if ($item['times_per_day'] > 0 && $item['minutes_per_day'] == 0) {
                        $item['minutes_per_day'] = 999999;
                    }
                    if ($item['times_per_day'] == 0 && $item['minutes_per_day'] > 0) {
                        $item['times_per_day'] = 999999;
                    }

                    return $item;
                });
            }

            // find pause code
            $pause_code = $pause_codes->where('code', $rec['Details'])->first();

            if (!$pause_code) {
                $rec_allowed_pause_time = 0;
            } else {

                // Increment count
                $pause_code->day_count++;

                // skip if over count or over duration
                if ($pause_code->day_count > $pause_code->times_per_day || $pause_code->day_duration >= ($pause_code->minutes_per_day * 60)) {
                    $rec_allowed_pause_time = 0;
                } else {

                    // figure out duration allowed
                    $tot_time = $pause_code->day_duration + $rec['Duration'];

                    // Only add to the total if campaign matches selected
                    if ($this->checkCampaign($rec['Campaign'])) {
                        if ($tot_time > ($pause_code->minutes_per_day * 60)) {
                            $rec_allowed_pause_time = ($pause_code->minutes_per_day * 60) - $pause_code->day_duration;
                        } else {
                            $rec_allowed_pause_time = $rec['Duration'];
                        }
                        $allowed_pause_time += $rec_allowed_pause_time;
                    }
                }
                // add to day duration
                $pause_code->day_duration += $rec['Duration'];
            }

            // There has got to be a better way to do this
            $rep_details->transform(function ($item) use ($rec, $rec_allowed_pause_time) {
                if ($item['id'] == $rec['id']) {
                    $item['AllowedPausedTime'] = $this->secondsToHms($rec_allowed_pause_time);
                }
                return $item;
            });
        }

        return $allowed_pause_time;
    }

    private function checkCampaign($campaign)
    {
        if (empty($this->campaign) || $this->campaign == 'Total') {
            return true;
        }

        foreach ((array) $this->campaign as $camp) {
            if ($camp == $campaign) {
                return true;
            }
        }

        return false;
    }
}
