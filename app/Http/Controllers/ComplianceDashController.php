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

    /**
     * Display dashboard for standalone
     *
     * @param Request $request
     * @return view
     */
    public function index(Request $request)
    {
        $this->getSession($request);

        $campaigns = $this->campaignGroups();

        $jsfile[] = "compliancedash.js";
        $cssfile[] = "compliancedash.css";

        $data = [
            'isApi' => $this->isApi,
            'campaign' => $this->campaign,
            'dateFilter' => $this->dateFilter,
            'campaign_list' => $campaigns,
            'curdash' => 'compliancedash',
            'jsfile' => $jsfile,
            'cssfile' => $cssfile,
        ];

        return view('compliancedash')->with($data);
    }

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

        return view('dashboards.compliance_settings')->with($data);
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

    public function updateFilters(Request $request)
    {
        Log::debug($request->all());
    }
}
