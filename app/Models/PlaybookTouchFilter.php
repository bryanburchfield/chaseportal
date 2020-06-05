<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaybookTouchFilter extends Model
{
    protected $fillable = [
        'playbook_touch_id',
        'playbook_filter_id',
    ];

    public function playbook_touch()
    {
        return $this->belongsTo('App\Models\PlaybookTouch');
    }

    public function playbook_filter()
    {
        return $this->belongsTo('App\Models\PlaybookFilter');
    }
}
