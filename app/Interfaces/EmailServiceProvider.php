<?php

namespace App\Interfaces;

interface EmailServiceProvider
{
    public function connect();

    public function testConnection();

    public function send($payload);

    public static function properties();
}
