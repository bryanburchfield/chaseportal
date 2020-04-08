<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Schedulable;
use OwenIt\Auditing\Contracts\Auditable;

class KpiGroup extends Model implements Auditable
{
    use Schedulable;
    use \OwenIt\Auditing\Auditable;

    public function kpi()
    {
        return $this->belongsTo('App\Models\Kpi');
    }
}
