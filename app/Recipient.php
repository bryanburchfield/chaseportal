<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Recipient extends Model
{
    public function kpiRecipients()
    {
        return $this->hasMany('App\KpiRecipient');
    }
}
