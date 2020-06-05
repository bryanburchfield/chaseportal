<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaybookTouchAction extends Model
{
    protected $fillable = [
        'playbook_touch_id',
        'playbook_action_id',
    ];

    public function playbook_touch()
    {
        return $this->belongsTo('App\Models\PlaybookTouch');
    }

    public function playbook_action()
    {
        return $this->belongsTo('App\Models\PlaybookAction');
    }
}
