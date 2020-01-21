<?php

namespace App\Interfaces\EmailProvider;

use App\Models\EmailServiceProvider;

interface EmailProvider
{
    public function __construct(EmailServiceProvider $email_serivce_provider);

    public function testConnection();

    public function send($message);
}
