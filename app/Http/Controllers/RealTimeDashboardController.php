<?php

namespace App\Http\Controllers;

use App\Models\Broadcast;
use App\Traits\SqlServerTraits;
use App\Traits\TimeTraits;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class RealTimeDashboardController extends Controller
{
    use SqlServerTraits;
    use TimeTraits;

    public function index()
    {
        // set channel name
        // 
        $channel = App::environment() . '.agent.' . Auth::user()->group_id . '.' . Auth::user()->db;

        $data = [
            'channel' => $channel,
            'data' => $this->runqueryAgent(Auth::user()->group_id, Auth::user()->db),
        ];

        // create db rec so cron will pick it up
        $broadcast = Broadcast::firstOrCreate(['channel' => $channel]);

        return view('test.rt_agent_dash')->with($data);
    }

    public function __call($function, $args)
    {
        $method = 'run' . $function;

        if (!method_exists($this, "{$method}")) {
            abort(404);
        }

        return $this->$method($args[0], $args[1]);
    }

    public function runqueryAgent($group_id, $db)
    {
        $sql = "SELECT 
            Login,
            Campaign,
            Subcampaign,
            Skill,
            SecondsInStatus,
            BreakCode,
            RTS.Caption as State,
            RTZ.Caption as Status
            FROM [$db].[dbo].[RealtimeStatistics_Agents] RTA WITH (SNAPSHOT)
            JOIN [$db].[dbo].[RealtimeStatistics_Agents_State] RTS ON RTS.State = RTA.State
            JOIN [$db].[dbo].[RealtimeStatistics_Agents_Status] RTZ ON RTZ.Status = RTA.Status
            WHERE RTA.GroupId = :groupid
            ORDER BY Login";

        $bind = [
            'groupid' => $group_id,
        ];

        $results = $this->runSql($sql, $bind, $db);

        foreach ($results as &$result) {
            $result['TimeInStatus'] = $this->secondsToHms($result['SecondsInStatus']);
        }

        return [
            'time' => now()->isoFormat('MMMM Do YYYY, h:mm:ss a'),
            'results' => $results,
        ];
    }
}
