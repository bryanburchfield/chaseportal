<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class FeatureMessage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'title',
        'body',
        'expires_at',
    ];

    public function readFeatureMessages()
    {
        return $this->hasMany('App\Models\ReadFeatureMessage');
    }
}
