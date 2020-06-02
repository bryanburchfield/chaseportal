<?php

namespace App\Http\Controllers;

use App\Models\PauseCode;
use App\Traits\DashTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
        Log::warning($request->all());
        Log::warning($request->rep);

        $this->getSession($request);

        $agent_detail = $this->getAgentCompliance($request->rep);

        return ['agent_detail' => [
            'agent_detail' => $agent_detail,
        ]];
    }

    private function getAgentCompliance($rep = null)
    {
        list($fromDate, $toDate) = $this->dateRange($this->dateFilter);

        // Back toDate up a second since it's not inclusive
        $toDate = $toDate->modify('-1 second');

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $sql = 'SET NOCOUNT ON;';

        $union = '';
        foreach ($this->databases as $i => $db) {
            $bind['group_id' . $i] =  Auth::user()->group_id;
            $bind['startdate' . $i] = $startDate;
            $bind['enddate' . $i] = $endDate;


            $sql .= " $union SELECT AA.id, AA.Rep, AA.Date, AA.Campaign, [Action], AA.Duration, AA.Details
            FROM [$db].[dbo].[AgentActivity] AA WITH(NOLOCK)";

            $sql .= "
            WHERE AA.GroupId = :group_id$i
            AND AA.Date >= :startdate$i
            AND AA.Date < :enddate$i";

            if ($rep !== null) {
                $sql .= " AND AA.Rep = :rep$i";
                $bind['rep' . $i] = $rep;
            }

            $union = 'UNION';
        }

        $sql .= " ORDER BY Rep, Date";

        Log::info($sql);
        Log::info($bind);

        list($results, $details) = $this->processResults($sql, $bind);

        if ($rep !== null) {
            return $details;
        }

        return $results;
    }

    private function processResults($sql, $bind)
    {
        // Only count work hours if logged in for selected campaign(s)
        // But count all pause recs since camp doesn't matter
        // We'll filter the pause data later

        $results = [];

        // loop thru results looking for log in/out times
        $detail_array = [];
        $tmparray = [];
        $blankrec = [
            'Rep' => '',
            'WorkedTime' => 0,
            'PausedTime' => 0,
            'PauseRecs' => [],
        ];

        $campaign_ok = false;
        $i = 0;
        foreach ($this->yieldSql($sql, $bind) as $rec) {
            if ($i == 0) {
                $i++;
                $tmparray[$i] = $blankrec;
                $tmparray[$i]['Rep'] = $rec['Rep'];
            } else {
                if ($rec['Rep'] != $tmparray[$i]['Rep']) {
                    $i++;
                    $tmparray[$i] = $blankrec;
                    $tmparray[$i]['Rep'] = $rec['Rep'];
                }
            }

            switch ($rec['Action']) {
                case 'Login':
                    $campaign_ok = $this->checkCampaign($rec['Campaign']);
                    if ($campaign_ok) {
                        $detail_array[] = $this->detailRec($rec);
                    }
                    break;
                case 'Logout':
                    if ($campaign_ok) {
                        $detail_array[] = $this->detailRec($rec);
                    }
                    $campaign_ok = false;
                    break;
                case 'Paused':
                    if (round($rec['Duration']) > 0) {
                        if ($campaign_ok) {
                            $tmparray[$i]['PausedTime'] += $rec['Duration'];
                        }
                        $tmparray[$i]['PauseRecs'][] = [
                            'id' => $rec['id'],
                            'Date' => substr($rec['Date'], 0, 26),  // strip offest
                            'Campaign' => $rec['Campaign'],
                            'Duration' => $rec['Duration'],
                            'Details' => $rec['Details'],
                        ];
                        $detail_array[] = $this->detailRec($rec);
                    }
                    break;
                default:
                    if ($campaign_ok) {
                        if ($rec['Duration'] > 0) {
                            $tmparray[$i]['WorkedTime'] += $rec['Duration'];
                            $detail_array[] = $this->detailRec($rec);
                        }
                    }
            }
        }

        // remove any rows that don't have paused time or WorkedTime
        $outerarray = [];
        foreach ($tmparray as $i => $rec) {
            if (round($rec['WorkedTime']) > 0 || round($rec['PausedTime']) > 0) {
                $outerarray[$i] = $rec;
                $outerarray[$i]['AllowedPausedTime'] = 0;
                $outerarray[$i]['TotWorkedTime'] = 0;
                $outerarray[$i]['PctWorked'] = 0;
            }
        }

        // Go thru pause recs adding manhours for allowed pause codes
        foreach ($outerarray as $rec) {
            $rec['AllowedPausedTime'] = $this->calcAllowedPausedTime($rec['PauseRecs']);

            // get rid of detailed pause recs
            unset($rec['PauseRecs']);

            // do calcs
            $rec['TotWorkedTime'] = $rec['WorkedTime'] + $rec['AllowedPausedTime'];
            $rec['PctWorked'] = number_format($rec['TotWorkedTime'] / ($rec['WorkedTime'] + $rec['PausedTime']) * 100, 2) . '%';
            $rec['PctWorkedInteger'] = round($rec['TotWorkedTime'] / ($rec['WorkedTime'] + $rec['PausedTime']) * 100);

            // format fields
            $rec['WorkedTime'] = $this->secondsToHms($rec['WorkedTime']);
            $rec['PausedTime'] = $this->secondsToHms($rec['PausedTime']);
            $rec['AllowedPausedTime'] = $this->secondsToHms($rec['AllowedPausedTime']);
            $rec['TotWorkedTime'] = $this->secondsToHms($rec['TotWorkedTime']);

            // TODO:  change this to actual route
            $rec['detail_link'] = action('ComplianceDashController@agentDetail', ['rep' => $rec['Rep']]);
            $results[] = $rec;
        }

        Log::debug($results);
        // Log::debug($detail_array);

        return [$results, $detail_array];
    }

    private function detailRec($rec)
    {
        $detail = [
            'id' => $rec['id'],
            'Date' => $rec['Date'],
            'Action' => $rec['Action'],
            'Details' => '',
            'WorkedTime' => 0,
            'PausedTime' => 0,
            'AllowedPausedTime' => 0,
        ];

        switch ($rec['Action']) {
            case 'Login':
                $detail['Details'] = $rec['Campaign'];
                break;
            case 'Logout':
                $detail['Details'] = $rec['Campaign'];
                break;
            case 'Paused':
                $detail['Details'] = $rec['Details'];
                $detail['PausedTime'] = $rec['Duration'];
                break;
            default:
                $detail['Workedtime'] = $rec['Duration'];
        }

        return $detail;
    }

    private function calcAllowedPausedTime(array $pause_recs)
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
                continue;
            }

            // Increment count
            $pause_code->day_count++;

            // skip if over count or over duration
            if ($pause_code->day_count > $pause_code->times_per_day || $pause_code->day_duration >= ($pause_code->minutes_per_day * 60)) {
                continue;
            }

            // figure out duration allowed
            $tot_time = $pause_code->day_duration + $rec['Duration'];

            // Only add to the total if campaign matches selected
            if ($this->checkCampaign($rec['Campaign'])) {
                if ($tot_time > ($pause_code->minutes_per_day * 60)) {
                    $allowed_pause_time += ($pause_code->minutes_per_day * 60) - $pause_code->day_duration;
                } else {
                    $allowed_pause_time += $rec['Duration'];
                }
            }

            // add to day duration
            $pause_code->day_duration += $rec['Duration'];
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
