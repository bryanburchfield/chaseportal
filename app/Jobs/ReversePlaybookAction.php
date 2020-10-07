<?php

namespace App\Jobs;

use App\Models\PlaybookRunTouchAction;
use App\Services\ContactsPlaybookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReversePlaybookAction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $playbook_run_touch_action;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(PlaybookRunTouchAction $playbook_run_touch_action)
    {
        $this->playbook_run_touch_action = $playbook_run_touch_action;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $service = new ContactsPlaybookService();

        // can only reverse lead actions
        if ($this->playbook_run_touch_action->playbook_action->action_type == 'lead') {
            $service->reverseLeadAction($this->playbook_run_touch_action);
        }
    }
}
