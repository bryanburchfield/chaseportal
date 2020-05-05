<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaybookRunDetail extends Model
{
    protected $fillable = [
        'playbook_run_id',
        'reporting_db',
        'lead_id',
    ];

    public function playbook_run()
    {
        return $this->belongsTo('App\Models\PlaybookRun');
    }
}
