<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaybookHistory extends Model
{
    protected $table = 'playbook_histories';
    protected $fillable = [
        'playbook_campaign_id',
        'reporting_db',
        'lead_id',
    ];
}
