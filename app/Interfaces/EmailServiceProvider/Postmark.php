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

    public function __construct(EmailServiceProvider $postmark_server)
    {
        $this->postmark_server = $postmark_server;
    }

    public function connect()
    {
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
    }

    public function test()
    {
        $payload = [
            'from' => $this->postmark_server->properties['default_signature'],
            'to' => "test@example.com",
            'subject' => "Testing",
            'body' => "Just testing Postmark API.",
        ];

        try {
            $this->connect();
            $sendResult = $this->send($payload);
        } catch (PostmarkException $e) {
            echo $e->message;
            $error = ValidationException::withMessages([
                'error' => [$e->message],
            ]);
            throw $error;
        } catch (\Exception $e) {
            echo $e->getMessage();
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
        return $this->client->sendEmail(
            $payload['from'],
            $payload['to'],
            $payload['subject'],
            $payload['body']
        );
    }

    public static function properties()
    {
        return [
            'api_token',
            'default_signature',
        ];
    }
}
