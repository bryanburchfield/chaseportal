<?php

namespace App\Services;

use App\Interfaces\EmailServiceProvider\Smtp;
use App\Models\EmailServiceProvider;
use Illuminate\Support\Str;

class EmailDripService
{
    private $email_service_provider;

    public function __construct(EmailServiceProvider $email_service_provider)
    {
        $class = Str::studly($email_service_provider->provider_type);

        $this->email_service_provider = new $class($email_service_provider);
    }

    public function testConnection()
    {
        return $this->email_service_provider->test();
    }
}
