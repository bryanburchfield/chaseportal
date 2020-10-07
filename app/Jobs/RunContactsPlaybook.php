<?php

namespace App\Jobs;

use App\Services\ContactsPlaybookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunContactsPlaybook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $contacts_playbook;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($contacts_playbook)
    {
        $this->contacts_playbook = $contacts_playbook;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $service = new ContactsPlaybookService();
        $service->runPlaybook($this->contacts_playbook);
    }
}
