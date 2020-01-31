<?php

namespace App\Services;

use App\Models\EmailServiceProvider;
use Illuminate\Support\Str;

class EmailDripService
{
    // Directory where Email Service Providers live
    // This is in the controller class too!
    const ESP_DIR = 'Interfaces\\EmailServiceProvider';

    private $email_service_provider;

    public function __construct(EmailServiceProvider $email_service_provider)
    {
        // full path the class so we don't have to import it
        $class = 'App\\' . self::ESP_DIR . '\\' .
            Str::studly($email_service_provider->provider_type);

        $this->email_service_provider = new $class($email_service_provider);
    }

    public function testConnection()
    {
        return $this->email_service_provider->testConnection();
    }
}
