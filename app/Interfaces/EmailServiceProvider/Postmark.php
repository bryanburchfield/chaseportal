<?php

namespace App\Interfaces\EmailServiceProvider;

use App\Models\EmailServiceProvider;
use Illuminate\Validation\ValidationException;

class Postmark implements \App\Interfaces\EmailServiceProvider
{
    private $postmark_server;

    public function __construct(EmailServiceProvider $postmark_server)
    {
        $this->postmark_server = $postmark_server;
    }

    public function connect()
    {
        # code...
    }

    public function test()
    {
        return [
            'status' => 'success',
            'message' => 'Connected Successfuly',
        ];
    }

    public function send($payload)
    {
        # code...
    }

    public static function properties()
    {
        return [
            'host',
            'apikey',
        ];
    }
}
