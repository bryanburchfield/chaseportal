<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class InternalSpamCheckMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct($data = [])
    {
        $this->data = $data;
    }

    public function build()
    {
        $datepart = now()->timezone('UTC')->format('Y-m-d_') . $this->data['period'] .  '.csv';

        $mainfilename = 'spamcheck-' . $datepart;

        return $this
            ->view('mail.internal_spam_check')
            ->subject($this->data['subject'])
            ->attachData(
                base64_decode($this->data['mainCsv']),
                $mainfilename,
                ['mime' => 'text/csv']
            );
    }
}
