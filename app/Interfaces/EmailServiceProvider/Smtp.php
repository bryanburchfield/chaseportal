<?php

namespace App\Interfaces\EmailServiceProvider;

use App\Models\EmailServiceProvider;
use Illuminate\Validation\ValidationException;
use Swift_Mailer;
use Swift_SmtpTransport;
use Swift_TransportException;

class Smtp implements \App\Interfaces\EmailServiceProvider
{
    private $smtp_server;

    public function __construct(EmailServiceProvider $smtp_server)
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
            $transport = (new Swift_SmtpTransport(
                $this->smtp_server->properties['host'],
                $this->smtp_server->properties['port'],
                'tls'
            ))
                ->setUsername($this->smtp_server->properties['username'])
                ->setPassword($this->smtp_server->properties['password']);

            $mailer = new Swift_Mailer($transport);
            $mailer->getTransport()->start();
            return [
                'status' => 'success',
                'message' => trans('tools.connection_successful'),
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

    public static function properties()
    {
        return [
            'host',
            'port',
            'username',
            'password',
        ];
    }
}
