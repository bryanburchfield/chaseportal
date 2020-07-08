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
}
