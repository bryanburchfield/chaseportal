<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureMessage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'title',
        'body',
        'created_at',
        'expires_at',
    ];

    public function readFeatureMessages()
    {
        return $this->hasMany('App\Models\ReadFeatureMessage');
    }
}
