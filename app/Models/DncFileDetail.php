<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DncFileDetail extends Model
{
    protected $fillable = [
        'dnc_id',
        'phone',
        'processed_at',
        'succeeded',
        'error',
    ];

    public function dncFile()
    {
        return $this->belongsTo('App\Models\DncFile');
    }
}
