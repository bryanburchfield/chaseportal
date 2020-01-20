<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;

class WelcomeDemoNotification extends Notification
{
    private $user;

    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $tz = $this->user->iana_tz;
        $expiration = Carbon::parse($this->user->expiration)->tz($tz)->toDayDateTimeString();

        $data = [
            'name' => $this->user->name,
            'expiration' => $expiration,
            'link' => url("/demo/" . $this->user->app_token),
            'url' => url('/') . '/',
        ];

        return (new MailMessage)
            ->subject('Welcome to ChaseData')
            ->view(
                'emails.welcomedemo',
                ['data' => $data]
            );
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
