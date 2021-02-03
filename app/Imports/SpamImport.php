<?php

namespace App\Imports;

use App\Models\SpamCheckBatch;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

abstract class SpamImport implements ToModel, WithChunkReading, WithBatchInserts
{
    protected $spam_check_batch_id;
    protected $column;
    protected $rows = 0;


    public function __construct($spam_check_batch_id, $column)
    {
        $this->spam_check_batch_id = $spam_check_batch_id;
        $this->column = $column;
    }

    public function model(array $row)
    {
        if (!array_key_exists($this->column, $row)) {
            return;
        }

        $this->rows++;

        $record = [];
        $record['spam_check_batch_id'] = $this->spam_check_batch_id;
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

        return new SpamCheckBatch($record);
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
