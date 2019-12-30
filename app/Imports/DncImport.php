<?php

namespace App\Imports;

use App\Models\DncFileDetail;
use Maatwebsite\Excel\Concerns\ToModel;

abstract class DncImport implements ToModel
{
    protected $dnc_file_id;
    protected $column;

    public function __construct($dnc_file_id, $column)
    {
        $this->dnc_file_id = $dnc_file_id;
        $this->column = $column;
    }

    public function model(array $row)
    {
        return new DncFileDetail([
            'dnc_file_id' => $this->dnc_file_id,
            'phone' => $row[$this->column],
        ]);
    }
}
