<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class KpiRecipient extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'kpi_id',
        'recipient_id',
    ];

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
