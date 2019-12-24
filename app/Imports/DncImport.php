<?php

namespace App\Imports;

use App\Models\DncFileDetail;
use Maatwebsite\Excel\Concerns\ToModel;

class DncImport implements ToModel
{
    protected $dnc_file_id;

    public function __construct($dnc_file_id)
    {
        $this->dnc_file_id = $dnc_file_id;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new DncFileDetail([
            'dnc_file_id' => $this->dnc_file_id,
            'phone' => $row[0],
        ]);
    }
}
