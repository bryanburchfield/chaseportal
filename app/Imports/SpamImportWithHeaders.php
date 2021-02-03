<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SpamImportWithHeaders extends SpamImport implements WithHeadingRow
{
    protected $header_adjustment = 1;
}
