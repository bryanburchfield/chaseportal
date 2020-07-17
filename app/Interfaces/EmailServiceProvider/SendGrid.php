<?php

namespace App\Interfaces\EmailServiceProvider;

use App\Models\EmailServiceProvider;
use Illuminate\Validation\ValidationException;

class SendGrid implements \App\Interfaces\EmailServiceProvider
{
    private $sendgrid_server;
    private $client;
    private $connected = 0;

    public function __construct(EmailServiceProvider $sendgrid_server)
    {
        $this->sendgrid_server = $sendgrid_server;
    }

    public function connect()
    {
        if ($this->connected) {
            return;
        }

        try {
            $this->client = new \SendGrid($this->sendgrid_server->properties['api_key']);
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
        // Don't call send() since that catches exceptions
        try {
            $this->connect();

            $email = new \SendGrid\Mail\Mail();
            $email->setFrom($this->sendgrid_server->properties['from_address']);
            $email->setSubject("Testing");
            $email->addTo("test@example.com", "Example User");
            $email->addContent("text/plain", "Just testing SendGrid API");

            $response = $this->client->send($email);
        } catch (\Exception $e) {
            $error = ValidationException::withMessages([
                'error' => [$e->getMessage()],
            ]);
            throw $error;
        }

        $body = json_decode($response->body());

        if (isset($body->errors)) {
            $error_array = [];
            foreach ($body->errors as $error) {
                $error_array[] = $error->message;
            }

            $error = ValidationException::withMessages([
                'error' => $error_array
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

            $email = new \SendGrid\Mail\Mail();
            $email->setFrom($payload['from']);
            $email->setSubject($payload['subject']);
            $email->addTo($payload['to']);
            $email->addCustomArg("tag", $payload['tag']);
            $email->addContent(
                "text/html",
                $payload['body']
            );

            $response = $this->client->send($email);

            return $response->statusCode();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public static function properties()
    {
        return [
            'api_key',
            'from_address',
        ];
    }

    public static function description()
    {
        return 'SendGrid';
    }
}
