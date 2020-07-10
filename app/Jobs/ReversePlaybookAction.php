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
    protected $user_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(PlaybookRunTouchAction $playbook_run_touch_action, $user_id)
    {
        $this->playbook_run_touch_action = $playbook_run_touch_action;
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $service = new ContactsPlaybookService();
        $service->reverseAction($this->playbook_run_touch_action, $this->user_id);
    }
}
