<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NearbyAreaCode extends Model
{
    protected $primaryKey = ['source_npa', 'nearby_npa'];

    public $incrementing = false;
    public $timestamps = false;

    protected $guarded = [];

    public function sourceAreaCode()
    {
        return $this->belongsTo('App\AreaCode', 'source_npa', 'npa');
    }

    public function nearbyAreaCode()
    {
        return $this->belongsTo('App\AreaCode', 'nearby_npa', 'npa');
    }
}
