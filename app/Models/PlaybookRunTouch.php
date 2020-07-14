<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaybookRunTouch extends Model
{
    protected $fillable = [
        'playbook_run_id',
        'playbook_touch_id',
    ];

    public function playbook_run()
    {
        return $this->belongsTo('App\Models\PlaybookRun');
    }

    public function playbook_touch()
    {
        return $this->belongsTo('App\Models\PlaybookTouch')->withTrashed();
    }

    public function playbook_run_touch_actions()
    {
        return $this->hasMany('App\Models\PlaybookRunTouchAction');
    }

    public function record_count()
    {
        $count = 0;
        foreach ($this->playbook_run_touch_actions as $playbook_run_touch_action) {
            $count += $playbook_run_touch_action->playbook_run_touch_action_details->count();
        }

        return $count;
    }
}
