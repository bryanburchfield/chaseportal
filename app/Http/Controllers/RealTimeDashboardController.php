<?php

namespace App\Http\Controllers;

use App\Models\Broadcast;
use App\Models\Lead;
use App\Traits\SqlServerTraits;
use App\Traits\TimeTraits;
use Illuminate\Support\Carbon;
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
        // Initialize all returns vals
        $talking = [];
        $wrapping = [];
        $waiting = [];
        $manual = [];
        $paused = [];
        $queued = 0;
        $handled = 0;
        $maxhold = 0;
        $sales = 0;

        // Get real-time agent stats
        $sql = "SET NOCOUNT ON;
        USE [$db];
        SELECT 
            RTA.Login,
            RTA.Campaign,
            RTA.Subcampaign,
            RTA.Skill,
            RTA.SecondsInStatus,
            RTA.BreakCode,
            RTA.State as StateCode,
            RTA.Status as StatusCode,
            RTS.Caption as State,
            RTZ.Caption as Status,
            RTA.Phone,
            RTA.LeadId
            FROM [RealtimeStatistics_Agents] RTA WITH (SNAPSHOT)
            JOIN [RealtimeStatistics_Agents_State] RTS ON RTS.State = RTA.State
            JOIN [RealtimeStatistics_Agents_Status] RTZ ON RTZ.Status = RTA.Status
            WHERE RTA.GroupId = :groupid
            ORDER BY RTA.SecondsInStatus DESC";

        $bind = [
            'groupid' => $group_id,
        ];

        $results = $this->runSql($sql, $bind, $db);

        foreach ($results as $result) {
            $result['TimeInStatus'] = $this->secondsToHms($result['SecondsInStatus']);
            $result['checksum'] = sprintf("%u", crc32(
                $result['Campaign'] .
                    $result['Subcampaign'] .
                    $result['Skill'] .
                    $result['BreakCode'] .
                    $result['State'] .
                    $result['Status'] .
                    $result['Phone'] .
                    $result['LeadId']
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

        // Get real-time inbound stats
        $sql = "SELECT
            SUM(CASE WHEN Agent = '' THEN 1 ELSE 0 END) as Handled,
            SUM(CASE WHEN Agent != '' THEN 1 ELSE 0 END) as Queued,
            MAX(CASE WHEN Agent = '' THEN HoldDuration ELSE 0 END) as MaxHold
            FROM [RealtimeStatistics_Inbound] WITH (SNAPSHOT)
            WHERE GroupId = :groupid
            AND Status = 3";

        unset($results);
        $results = $this->runSql($sql, $bind, $db);

        if (count($results)) {
            $queued = $results[0]['Handled'];
            $handled = $results[0]['Queued'];
            $maxhold = $results[0]['MaxHold'];


            // Change NULLs to 0s
            $queued = ($queued == null) ? 0 : $queued;
            $handled = ($handled == null) ? 0 : $handled;
            $maxhold = ($maxhold == null) ? 0 : $maxhold;
        }

        // Get sales for the day
        $bind['startdate'] = date('Y-m-d');

        $sql = "SELECT
            SUM(CASE WHEN DI.Type = '3' THEN 1 ELSE 0 END) as Sales
            FROM [DialingResults] DR
            INNER JOIN [$db].[dbo].[Dispos] DI ON DI.id = DR.DispositionId
            WHERE DR.GroupId = :groupid
            AND DR.Date >= :startdate";

        unset($results);
        $results = $this->runSql($sql, $bind, $db);

        if (count($results)) {
            $sales = $results[0]['Sales'];

            // Change NULL to 0
            $sales = ($sales == null) ? 0 : $sales;
        }

        return [
            'statuses' => [
                'talking' => $talking,
                'wrapping' => $wrapping,
                'waiting' => $waiting,
                'manual' => $manual,
                'paused' => $paused,
            ],
            'queued' => $queued,
            'handled' => $handled,
            'maxhold' => $maxhold,
            'sales' => $sales,
        ];
    }

    public function getLeadInfo(Lead $lead)
    {
        // Check that lead belongs to this group
        if ($lead->GroupId != Auth::user()->group_id) {
            abort(403, 'Unauthorized');
        }

        $info = $lead->allFields();

        $tz = Auth::user()->ianaTz;

        // Don't care to see these fields:
        unset($info['DispositionId']);
        unset($info['ReloadDate']);
        unset($info['ReloadAttempt']);

        // Format Dates:
        $info['Date'] = Carbon::parse($info['Date'])
            ->tz($tz)
            ->isoFormat('L LT');

        $info['LastUpdated'] = Carbon::parse($info['LastUpdated'])
            ->tz($tz)
            ->isoFormat('L LT');

        if (!empty($info['Notes'])) {
            $info['Notes'] = preg_replace(
                '/(\d{2}-\d{2}-\d{4} \d{2}:\d{2} : )/',
                "</p></div><div class=\"real_time_contact_note mb10\"><span class=\"fw600\">$1</span><p>",
                $info['Notes']
            ) . '</div>';

            $info['Notes'] = substr($info['Notes'], 10);
        }

        return $info;
    }
}
