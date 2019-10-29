<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DbLeadmove extends Model
{
    protected $fillable = [
        'lead_id',
        'db_from',
        'db_to',
        'process_id',
        'succeeded',
    ];
}
