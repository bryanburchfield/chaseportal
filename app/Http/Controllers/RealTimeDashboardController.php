<?php

namespace App\Http\Controllers;

use App\Models\Broadcast;
use App\Jobs\StartBroadcast;
use App\Traits\SqlServerTraits;
use Illuminate\Support\Facades\App;

class RealTimeDashboardController extends Controller
{
    use SqlServerTraits;

    public function index()
    {
        // set channel name
        // this will execute query{$Channel}()   eg. queryHome()
        $channel = App::environment() . '-agent';

        $data = [
            'channel' => $channel,
            'data' => $this->queryAgent(),
        ];

        // create db rec so cron will pick it up
        $broadcast = Broadcast::firstOrCreate(['channel' => $channel]);

        // if this is the first listener, start broadcasting the query - with a delay
        if ($broadcast->wasRecentlyCreated) {
            StartBroadcast::dispatch($channel, true);
        }

        return view('test.rt_agent_dash')->with($data);
    }

    public function queryAgent()
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
            FROM RealtimeStatistics_Agents RTA WITH (SNAPSHOT)
            JOIN RealtimeStatistics_Agents_State RTS ON RTS.State = RTA.State
            JOIN RealtimeStatistics_Agents_Status RTZ ON RTZ.Status = RTA.Status
            WHERE RTA.GroupId = :groupid";

        $bind = [
            'groupid' => 777,
        ];

        $results = $this->runSql($sql, $bind, 'PowerV2_Reporting_Dialer-07');

        foreach ($results as &$result) {
            $result['TimeInStatus'] = gmdate("H:i:s", $result['SecondsInStatus']);
        }

        return [
            'time' => now()->isoFormat('MMMM Do YYYY, h:mm:ss a'),
            'results' => $results,
        ];
    }
}
