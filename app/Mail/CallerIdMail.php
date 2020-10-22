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
        $filename = 'callerid-' . now()->timezone('America/New_York')->format('Y-m-d') . '.csv';

        return $this
            ->view('mail.callerid')
            ->subject($this->data['subject'])
            ->attachData(
                base64_decode($this->data['csv']),
                $filename,
                [
                    'mime' => 'text/csv',
                ]
            );
    }
}
