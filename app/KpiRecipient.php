<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KpiRecipient extends Model
{
    public $timestamps = false;

    public function kpi()
    {
        return $this->belongsTo('App\Kpi');
    }

    public function recipient()
    {
        return $this->belongsTo('App\Recipient');
    }

    public function groupId()
    {
        return $this->recipient->group_id;
    }
}
