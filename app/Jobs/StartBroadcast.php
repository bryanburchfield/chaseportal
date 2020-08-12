<?php

namespace App\Jobs;

use App\Services\Broadcaster;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StartBroadcast implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $channel;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($channel, $delay = false)
    {
        $this->channel = $channel;

        if ($delay) {
            sleep(1);
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Broadcaster::runChannel($this->channel);
    }
}
