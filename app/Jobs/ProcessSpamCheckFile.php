<?php

namespace App\Jobs;

use App\Models\SpamCheckBatch;
use App\Services\SpamCheckService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessSpamCheckFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 7200;   // 2hrs

    /**
     * The queue connection that should handle the job.
     *
     * @var string
     */
    public $connection = 'spamcheck';

    protected $spamCheckBatch;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(SpamCheckBatch $spamCheckBatch)
    {
        $this->spamCheckBatch = $spamCheckBatch;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $service = new SpamCheckService();
        $service->processFile($this->spamCheckBatch);
    }

    /**
     * Determine the time at which the job should timeout.
     *
     * @return \DateTime
     */
    public function retryUntil()
    {
        return now()->addMinutes(120);
    }
}
