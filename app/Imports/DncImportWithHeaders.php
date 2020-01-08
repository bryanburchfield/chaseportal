<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DncImportWithHeaders extends DncImport implements WithHeadingRow
{
    protected $header_adjustment = 1;
}
