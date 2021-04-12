<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpamCheckBatchDetail extends Model
{
    protected $fillable = [
        'spam_check_batch_id',
        'line',
        'phone',
        'succeeded',
        'error',
        'checked',
        'flagged',
        'flags',
    ];

    public function spamcCheckBatch()
    {
        return $this->belongsTo('App\Models\SpamCheckBatch');
    }
}
