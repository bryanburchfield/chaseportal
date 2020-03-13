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
}
