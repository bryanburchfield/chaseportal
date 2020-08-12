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
        $self->checkForChannels();
    }

    public static function runChannel($channel)
    {
        $self = new self();
        $self->startBroadcasting($channel);
    }

    public function checkForChannels()
    {
        $channels = Broadcast::all();

        foreach ($channels as $channel) {
            StartBroadcast::dispatch($channel->channel);
        }
    }

    private function startBroadcasting($channel)
    {
        $controller = new RealTimeDashboardController();

        $thisMinute = $this->getMinute();

        list($env, $queryMethod, $group_id, $db) = explode('.', $channel);

        $queryMethod = 'query' . Str::title($queryMethod);

        $i = 0;
        while ($this->isOccupied($channel)) {
            $i++;

            // bail if minute changes
            if ($thisMinute !== $this->getMinute()) {
                break;
            }

            // get data
            $data = $controller->$queryMethod($group_id, $db);

            event(new NewMessage($channel, $data));

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

        // delete record if not occupied
        if (!$occupied) {
            Broadcast::where('channel', $channel)->delete();
        }

        return $occupied;
    }
}
