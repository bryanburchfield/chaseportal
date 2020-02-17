<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReportMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $data;
    public $pdf;

    public function __construct($data = [])
    {
        $this->data = $data;
    }

    public function build()
    {
        return $this
            ->view('mail.report')
            ->subject($this->data['subject'])
            ->attachData(
                base64_decode($this->data['pdf']),
                'report.pdf',
                [
                    'mime' => 'application/pdf',
                ]
            );
    }
}
