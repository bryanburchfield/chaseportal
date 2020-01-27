<?php

namespace App\Services;

use App\Interfaces\EmailServiceProvider\Smtp;
use App\Models\SmtpServer;

class EmailDripService
{
    private $email_service_provider;

    public function __construct(SmtpServer $smtp_server)
    {
        // Obviously, this will change with new ESP types
        $this->email_service_provider = new Smtp($smtp_server);
    }

    public function testConnection()
    {
        return $this->email_service_provider->test();
    }
}
