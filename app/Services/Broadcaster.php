<?php

namespace App\Services;

use App\Models\Broadcast;
use App\Events\NewMessage;
use App\Http\Controllers\RealTimeDashboardController;
use App\Jobs\StartBroadcast;
use Exception;
use Illuminate\Support\Str;
use Pusher\Pusher;

class Broadcaster
{
    private $pusher;

    public function __construct()
    {
        $this->initializePusher();
    }

    public static function run()
    {
        $self = new self();
        $self->loopForMinute();
    }

    public function loopForMinute()
    {
        $controller = new RealTimeDashboardController();

        $thisMinute = $this->getMinute();

        while ($thisMinute == $this->getMinute()) {

            foreach (Broadcast::all() as $channel) {
                list($env, $queryMethod, $group_id, $db) = explode('.', $channel->channel);

                $queryMethod = 'query' . Str::title($queryMethod);

                if ($this->isOccupied($channel->channel)) {
                    $data = $controller->$queryMethod($group_id, $db);
                    event(new NewMessage($channel->channel, $data));
                } else {
                    // delete if older than 10 secs
                    if (now()->diffInSeconds($channel->created_at) > 5) {
                        $channel->delete();
                    }
                }
            }

            sleep(3);
        }
    }

    private function initializePusher()
    {
        $connection = config('broadcasting.connections.pusher');

        $this->pusher = new Pusher(
            $connection['key'],
            $connection['secret'],
            $connection['app_id'],
            $connection['options']
        );
    }

    private function getMinute()
    {
        return now()->toObject()->minute;
    }

    private function isOccupied($channel)
    {
        try {
            $result = $this->pusher->get('/channels/' . $channel);
        } catch (Exception $e) {
            $result = false;
        }

        $occupied = isset($result['result']['occupied']) ? $result['result']['occupied'] : false;

        return $occupied;
    }
}
