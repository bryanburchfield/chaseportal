<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReadFeatureMessage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'feature_message_id',
        'user_id',
        'read_at',
    ];

    public function featureMessage()
    {
        return $this->belongsTo('App\Models\FeatureMessage');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
