<?php

namespace App\Jobs;

use App\LeadMove;
use App\Services\LeadMoveService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReverseLeadMove implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $lead_move;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(LeadMove $lead_move)
    {
        $this->lead_move = $lead_move;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        LeadMoveService::reverseMove($this->lead_move);
    }
}
