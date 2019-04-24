<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KpiGroup extends Model
{
    public $timestamps = false;

    public function kpi()
    {
        return $this->belongsTo('App\Kpi');
    }
}
