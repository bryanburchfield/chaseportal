<?php

namespace App\Interfaces\EmailServiceProvider;

use App\Models\EmailServiceProvider;
use Illuminate\Validation\ValidationException;
use Postmark\PostmarkClient;
use Postmark\Models\PostmarkException;

class Postmark implements \App\Interfaces\EmailServiceProvider
{
    private $postmark_server;
    private $client;
    private $connected = 0;

    public function __construct(EmailServiceProvider $postmark_server)
    {
        $this->postmark_server = $postmark_server;
    }

    public function connect()
    {
        if ($this->connected) {
            return;
        }

        try {
            $this->client = new PostmarkClient($this->postmark_server->properties['api_token']);
        } catch (PostmarkException $e) {
            $error = ValidationException::withMessages([
                'error' => [$e->message],
            ]);
            throw $error;
        } catch (\Exception $e) {
            $error = ValidationException::withMessages([
                'error' => [$e->getMessage()],
            ]);
            throw $error;
        }

        $this->connected = 1;
    }

    public function testConnection()
    {
        $payload = [
            'from' => $this->postmark_server->properties['default_signature'],
            'to' => "test@example.com",
            'subject' => "Testing",
            'body' => "Just testing Postmark API.",
        ];

        try {
            $sendResult = $this->send($payload);
        } catch (PostmarkException $e) {
            $error = ValidationException::withMessages([
                'error' => [$e->message],
            ]);
            throw $error;
        } catch (\Exception $e) {
            $error = ValidationException::withMessages([
                'error' => [$e->getMessage()],
            ]);
            throw $error;
        }

        return [
            'status' => 'success',
            'message' => trans('tools.connection_successful'),
        ];
    }

    public function send($payload)
    {
        try {
            $this->connect();

            return $this->client->sendEmail(
                $payload['from'],
                $payload['to'],
                $payload['subject'],
                $payload['body']
            );
        } catch (PostmarkException $e) {
            return $e->message;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public static function properties()
    {
        return [
            'api_token',
            'default_signature',
        ];
    }
}
