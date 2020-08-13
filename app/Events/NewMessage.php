<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// use Pusher\Pusher;

class NewMessage implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $channel;
    public $message;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($channel, $message)
    {
        $this->channel = $channel;
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        echo '==== NewMessage ' . $this->channel . "====\n";
        return new Channel($this->channel);
    }
}
