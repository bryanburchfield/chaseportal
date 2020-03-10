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
        'active',
    ];

    public function readFeatureMessages()
    {
        return $this->hasMany('App\Models\ReadFeatureMessage');
    }

    public function getMarkDownAttribute($value)
    {
        return (new \Parsedown)->text($this->body);
    }

    public function getTextBodyAttribute($value)
    {
        return strip_tags((new \Parsedown)->text($this->body));
    }
}
