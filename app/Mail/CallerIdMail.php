<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CallerIdMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct($data = [])
    {
        $this->data = $data;
    }

    public function build()
    {
        return $this
            ->view('mail.callerid')
            ->subject($this->data['subject'])
            ->attachData(
                base64_decode($this->data['pdf']),
                'callerid.pdf',
                [
                    'mime' => 'application/pdf',
                ]
            );
    }
}
