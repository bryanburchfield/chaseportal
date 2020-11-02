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
        $datepart = now()->timezone('America/New_York')->format('Y-m-d') . '.csv';

        $mainfilename = 'callerid-' . $datepart;
        // $autoswapfilename = 'callerid-autoswap-' . $datepart;
        // $manualswapfilename = 'callerid-manualswap-' . $datepart;
        // $othersfilename = 'callerid-others-' . $datepart;

        return $this
            ->view('mail.callerid')
            ->subject($this->data['subject'])
            ->attachData(
                base64_decode($this->data['mainCsv']),
                $mainfilename,
                ['mime' => 'text/csv']
            );
        // ->attachData(
        //     base64_decode($this->data['autoswapCsv']),
        //     $autoswapfilename,
        //     ['mime' => 'text/csv']
        // )
        // ->attachData(
        //     base64_decode($this->data['manualswapCsv']),
        //     $manualswapfilename,
        //     ['mime' => 'text/csv']
        // )
        // ->attachData(
        //     base64_decode($this->data['othersCsv']),
        //     $othersfilename,
        //     ['mime' => 'text/csv']
        // );
    }
}
