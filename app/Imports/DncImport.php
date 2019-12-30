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
    private $rows = 0;

    public function __construct($dnc_file_id, $column)
    {
        $this->dnc_file_id = $dnc_file_id;
        $this->column = $column;
    }

    public function model(array $row)
    {
        $this->rows++;

        return new DncFileDetail([
            'dnc_file_id' => $this->dnc_file_id,
            'phone' => preg_replace("/[^0-9]/", '', $row[$this->column]),
        ]);
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
