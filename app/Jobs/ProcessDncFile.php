<?php

namespace App\Jobs;

use App\Models\DncFile;
use App\Services\DncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessDncFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $dnc_file;
    protected $user_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(DncFile $dnc_file, $user_id)
    {
        $this->dnc_file = $dnc_file;
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $service = new DncService($this->user_id);
        $service->processFile($this->dnc_file);
    }
}
