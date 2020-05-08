<?php

namespace App\Interfaces\EmailServiceProvider;

use App\Models\EmailServiceProvider;
use Illuminate\Validation\ValidationException;
use Swift_Mailer;
use Swift_Message;
use Swift_Plugins_AntiFloodPlugin;
use Swift_SmtpTransport;
use Swift_TransportException;

class Smtp implements \App\Interfaces\EmailServiceProvider
{
    private $smtp_server;
    private $mailer;
    private $connected = 0;

    public function __construct(EmailServiceProvider $smtp_server)
    {
        $this->smtp_server = $smtp_server;
    }

    public function connect()
    {
        if ($this->connected) {
            return;
        }

        $transport = (new Swift_SmtpTransport(
            $this->smtp_server->properties['host'],
            $this->smtp_server->properties['port'],
            'tls'
        ))
            ->setUsername($this->smtp_server->properties['username'])
            ->setPassword($this->smtp_server->properties['password']);

        $this->mailer = new Swift_Mailer($transport);
        $this->mailer->registerPlugin(new Swift_Plugins_AntiFloodPlugin(100));

        $this->connected = 1;
    }

    public function testConnection()
    {
        // see if we can connect to server
        try {
            $this->connect();

            $this->mailer->getTransport()->start();

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
        try {
            $this->connect();

            $message = (new Swift_Message($payload['subject']))
                ->setFrom($payload['from'])
                ->setTo($payload['to'])
                ->setBody($payload['body'], 'text/html');

            // Send the message
            return $this->mailer->send($message);
        } catch (Swift_TransportException $e) {
            return $e->getMessage();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
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

    public static function description()
    {
        return 'SMTP Server';
    }
}
