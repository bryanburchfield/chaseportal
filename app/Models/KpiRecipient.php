<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiRecipient extends Model
{
    public function kpi()
    {
        return $this->belongsTo('App\Models\Kpi');
    }

    public function recipient()
    {
        return $this->belongsTo('App\Models\Recipient');
    }

    public function groupId()
    {
        return $this->recipient->group_id;
    }
}
