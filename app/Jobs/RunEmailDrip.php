<?php

namespace App\Jobs;

use App\Models\EmailDripCampaign;
use App\Models\EmailServiceProvider;
use App\Services\EmailDripService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunEmailDrip implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $email_drip_campaign;
    protected $email_service_provider;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(EmailDripCampaign $email_drip_campaign, EmailServiceProvider $email_service_provider)
    {
        $this->email_drip_campaign = $email_drip_campaign;
        $this->email_service_provider = $email_service_provider;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $email_drip_service = new EmailDripService($this->email_service_provider);
        $email_drip_service->runDrip($this->email_drip_campaign);
    }
}
