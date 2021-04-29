<?php

namespace App\Imports;

use App\Http\Controllers\SpamCheckController;
use App\Models\SpamCheckBatchDetail;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

abstract class SpamImport implements ToModel, WithChunkReading, WithBatchInserts
{
    protected $spam_check_batch_id;
    protected $column;
    protected $rows = 0;
    protected $spamCheckController;


    public function __construct($spam_check_batch_id, $column)
    {
        $this->spam_check_batch_id = $spam_check_batch_id;
        $this->column = $column;

        $this->spamCheckController = new SpamCheckController;
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

        // sometimes newlines get imported
        $record['phone'] = str_replace('_x000D_', '', $row[$this->column]);

        // validate number
        $record['error'] = null;
        if (!$this->spamCheckController->validNaPhone($record['phone'])) {
            $record['error'] = 'Invalid phone number';
        }
        $record['succeeded'] = empty($record['error']);

        return new SpamCheckBatchDetail($record);
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
