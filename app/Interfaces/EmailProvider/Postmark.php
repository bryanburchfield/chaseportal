<?php

namespace App\Interfaces\EmailProvider;

use App\Models\EmailServiceProvider;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class Postmark implements EmailProvider
{
    public $email_serivce_provider;

    private $verify_ssl = true;
    private $timeout = 30;
    private $authorization_token;
    private $authorization_header;
    private $version;
    private $os;
    private $client;

    public function __construct(EmailServiceProvider $email_serivce_provider)
    {
        $this->email_serivce_provider = $email_serivce_provider;

        $this->authorization_header = 'X-Postmark-Server-Token';
        $this->authorization_token = $email_serivce_provider->password;
        $this->version = phpversion();
        $this->os = PHP_OS;
    }

    public function testConnection()
    {
        // Postmark lets you send test emails that won’t actually get delivered to the recipient.
        // You can do this by passing POSTMARK_API_TEST as your server API token.

        // curl "https://api.postmarkapp.com/email" \
        // -X POST \
        // -H "Accept: application/json" \
        // -H "Content-Type: application/json" \
        // -H "X-Postmark-Server-Token: server token" \
        // -d "{From: 'sender@example.com',
        //      To: 'receiver@example.com',
        //      Subject: 'Postmark test',
        //      HtmlBody: '<html><body><strong>Hello</strong> dear Postmark user.</body></html>'}"


    }

    function send($message)
    {
        # code...
    }

    private function apiSend($payload)
    {
        # code...
    }

    private function getClient()
    {
        if (!$this->client) {
            $this->client = new Client([
                RequestOptions::VERIFY  => $this->verify_ssl,
                RequestOptions::TIMEOUT => $this->timeout,
            ]);
        }
        return $this->client;
    }
}
