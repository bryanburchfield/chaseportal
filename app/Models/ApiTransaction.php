<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiTransaction extends Model
{
    protected $guarded = [];

    public function api()
    {
        return $this->belongsTo('App\Models\Api');
    }
}
