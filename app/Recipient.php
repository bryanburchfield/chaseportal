<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Recipient extends Model
{
    public $timestamps = false;

    public function kpiRecipients()
    {
        return $this->hasMany('App\KpiRecipient');
    }
}
