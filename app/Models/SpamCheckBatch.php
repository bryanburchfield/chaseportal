<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpamCheckBatch extends Model
{
    protected $fillable = [
        'user_id',
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
