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

    /**
     * Create model from imported row
     * 
     * @param array $row 
     * @return App\Models\DncFileDetail 
     */
    public function model(array $row)
    {
        if (!array_key_exists($this->column, $row)) {
            return;
        }

        $this->rows++;

        $record = [];
        $record['dnc_file_id'] = $this->dnc_file_id;
        $record['line'] = $this->rows + $this->header_adjustment;

        // strip non-digits from phone number
        $record['phone'] = preg_replace("/[^0-9]/", '', $row[$this->column]);

        // Vary basic validation
        if (strlen($record['phone']) < 7) {
            $record['succeeded'] = false;
            $record['error'] = trans('tools.invalid_phone');
        } else {
            $record['succeeded'] = null;
            $record['error'] = null;
        }

        return new DncFileDetail($record);
    }

    /**
     * Set chunk size for reading from file
     * 
     * @return int 
     */
    public function chunkSize(): int
    {
        return 500;
    }

    /**
     * Set batch size for inserting into db
     * 
     * @return int 
     */
    public function batchSize(): int
    {
        return 500;
    }
}
