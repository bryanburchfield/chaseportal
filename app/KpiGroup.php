<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Schedulable;

class KpiGroup extends Model
{
    use Schedulable;

    public function kpi()
    {
        return $this->belongsTo('App\Kpi');
    }
}
