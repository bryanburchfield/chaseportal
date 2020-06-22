<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaybookRunTouchActionDetail extends Model
{
    protected $fillable = [
        'playbook_run_touch_action_id',
        'reporting_db',
        'lead_id',
    ];

    public function playbook_run_touch_action()
    {
        return $this->belongsTo('App\Models\PlaybookRunTouchAction');
    }
}
