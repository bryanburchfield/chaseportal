<?php

namespace App\Imports;

use App\Models\DncFileDetail;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

abstract class DncImport implements ToModel, WithChunkReading, WithBatchInserts
{
    protected $dnc_file_id;
    protected $column;
    protected $rows = 0;

    public function __construct($dnc_file_id, $column)
    {
        $this->dnc_file_id = $dnc_file_id;
        $this->column = $column;
    }

    public function model(array $row)
    {
        $this->rows++;

        $record = [];
        $record['dnc_file_id'] = $this->dnc_file_id;
        $record['line'] = $this->rows + $this->header_adjustment;

        // strip non-digits from phone number
        $record['phone'] = preg_replace("/[^0-9]/", '', $row[$this->column]);

        // if it's 11 digits and begins with a 1, strip the 1
        if (strlen($record['phone']) == 11 && substr($record['phone'], 0, 1) == '1') {
            $record['phone'] = substr($record['phone'], 1);
        }

        // Vary basic validation
        if (strlen($record['phone']) !== 10) {
            $record['succeeded'] = false;
            $record['error'] = 'Invalid phone number';
        } else {
            $record['succeeded'] = null;
            $record['error'] = null;
        }

        return new DncFileDetail($record);
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function batchSize(): int
    {
        return 500;
    }

    public function getcount()
    {
        return $this->rows;
    }
}
