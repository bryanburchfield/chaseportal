<?php

namespace App\Interfaces;

interface EmailServiceProvider
{
    public function connect();

    public function test();

    public function send($payload);
}
