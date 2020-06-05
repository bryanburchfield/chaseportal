<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaybookRunTouchAction extends Model
{
    protected $fillable = [
        'playbook_run_touch_id',
        'playbook_action_id',
    ];

    public function playbook_run_touch()
    {
        return $this->belongsTo('App\Models\PlaybookRunTouch');
    }

    public function playbook_action()
    {
        return $this->belongsTo('App\Models\PlaybookAction');
    }

    public function playbook_run_touch_action_details()
    {
        return $this->hasMany('App\Models\PlaybookRunTouchActionDetail');
    }
}
