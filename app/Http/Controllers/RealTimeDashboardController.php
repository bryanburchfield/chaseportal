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

        return $data;
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
            RTA.Login,
            RTA.Campaign,
            RTA.Subcampaign,
            RTA.Skill,
            RTA.SecondsInStatus,
            RTA.BreakCode,
            RTA.State as StateCode,
            RTA.Status as StatusCode,
            RTS.Caption as State,
            RTZ.Caption as Status
            FROM [$db].[dbo].[RealtimeStatistics_Agents] RTA WITH (SNAPSHOT)
            JOIN [$db].[dbo].[RealtimeStatistics_Agents_State] RTS ON RTS.State = RTA.State
            JOIN [$db].[dbo].[RealtimeStatistics_Agents_Status] RTZ ON RTZ.Status = RTA.Status
            WHERE RTA.GroupId = :groupid
            ORDER BY RTA.SecondsInStatus DESC";

        $bind = [
            'groupid' => $group_id,
        ];

        $results = $this->runSql($sql, $bind, $db);

        // split into different tables
        $talking = [];
        $wrapping = [];
        $waiting = [];
        $manual = [];
        $paused = [];

        foreach ($results as $result) {
            $result['TimeInStatus'] = $this->secondsToHms($result['SecondsInStatus']);
            $result['checksum'] = sprintf("%u", crc32(
                $result['Campaign'] .
                    $result['Subcampaign'] .
                    $result['Skill'] .
                    $result['BreakCode'] .
                    $result['State'] .
                    $result['Status']
            ));

            switch ($result['StatusCode']) {
                case 0:
                    $waiting[] = $result;
                    break;
                case 1:
                    $paused[] = $result;
                    break;
                case 2:
                    $wrapping[] = $result;
                    break;
                case 3:
                    $talking[] = $result;
                    break;
                case 4:
                    $manual[] = $result;
                    break;
                case 5:
                    $talking[] = $result;
                    break;
            }
        }

        return [
            'talking' => $talking,
            'wrapping' => $wrapping,
            'waiting' => $waiting,
            'manual' => $manual,
            'paused' => $paused,
        ];
    }
}
