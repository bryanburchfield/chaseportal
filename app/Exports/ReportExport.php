<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class ReportExport implements FromArray
{
    protected $results;

    public function __construct(array $results)
    {
        $this->results = $results;
    }

    public function array(): array
    {
        return $this->results;
    }
}
