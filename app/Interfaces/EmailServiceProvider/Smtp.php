<?php

namespace App\Interfaces\EmailServiceProvider;

use App\Interfaces\EmailServiceProvider;
use App\Models\SmtpServer;
use Illuminate\Validation\ValidationException;
use Swift_Mailer;
use Swift_SmtpTransport;
use Swift_TransportException;

class Smtp implements EmailServiceProvider
{
    private $smtp_server;

    public function __construct(SmtpServer $smtp_server)
    {
        $this->smtp_server = $smtp_server;
    }

    public function connect()
    {
        # code...
    }

    public function test()
    {
        // see if we can connect to server
        try {
            $transport = (new Swift_SmtpTransport($this->smtp_server->host, $this->smtp_server->port, 'tls'))
                ->setUsername($this->smtp_server->username)
                ->setPassword($this->smtp_server->password);

            $mailer = new Swift_Mailer($transport);
            $mailer->getTransport()->start();
            return [
                'status' => 'success',
                'message' => 'Connected Successfuly',
            ];
        } catch (Swift_TransportException $e) {
            $error = ValidationException::withMessages([
                'error' => [$e->getMessage()],
            ]);
            throw $error;
        } catch (\Exception $e) {
            $error = ValidationException::withMessages([
                'error' => [$e->getMessage()],
            ]);
            throw $error;
        }
    }

    public function send($payload)
    {
        # code...
    }
}
