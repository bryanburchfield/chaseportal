<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpamCheckBatch extends Model
{
    protected $fillable = [
        'user_id',
        'description',
        'uploaded_at',
        'process_started_at',
        'processed_at',
    ];

    public function spamCheckBatchDetails()
    {
        return $this->hasMany('App\Models\SpamCheckBatchDetail');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
