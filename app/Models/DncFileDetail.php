<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DncFileDetail extends Model
{
    protected $fillable = [
        'dnc_file_id',
        'line',
        'phone',
        'processed_at',
        'succeeded',
        'error',
    ];

    public $timestamps = false;

    public function dncFile()
    {
        return $this->belongsTo('App\Models\DncFile');
    }
}
