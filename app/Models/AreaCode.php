<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AreaCode extends Model
{
    protected $primaryKey = 'npa';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    public function nearbyAreaCodes()
    {
        return $this->hasMany('App\Models\NearbyAreaCode', 'source_npa');
    }

    public function alternateNpas()
    {
        return DB::select(
            DB::raw('SELECT A2.*
FROM nearby_area_codes N
INNER JOIN area_codes A1 ON A1.npa = N.source_npa
INNER JOIN area_codes A2 ON A2.npa = N.nearby_npa
WHERE A1.npa = :npa
AND A1.state = A2.state'),
            ['npa' => $this->npa]
        );
    }
}
