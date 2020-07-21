<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class PlaybookRunTouchAction extends Model
{
    protected $fillable = [
        'playbook_run_touch_id',
        'playbook_action_id',
        'process_started_at',
        'processed_at',
        'reverse_started_at',
        'reversed_at',
    ];

    public function playbook_run_touch()
    {
        return $this->belongsTo('App\Models\PlaybookRunTouch');
    }

    public function playbook_action()
    {
        return $this->belongsTo('App\Models\PlaybookAction')->withTrashed();
        // return $this->belongsTo('App\Models\PlaybookAction');
    }

    public function playbook_run_touch_action_details()
    {
        return $this->hasMany('App\Models\PlaybookRunTouchActionDetail');
    }

    public function getProcessedAtAttribute($date)
    {
        if (empty($date)) {
            return $date;
        }

        if (Auth::check()) {
            return Carbon::createFromFormat('Y-m-d H:i:s', $date)->copy()->tz(Auth::user()->iana_tz)->isoFormat('L LT');
        } else {
            return Carbon::createFromFormat('Y-m-d H:i:s', $date)->copy()->tz('America/New_York')->isoFormat('L LT');
        }
    }

    public function getReversedAtAttribute($date)
    {
        if (empty($date)) {
            return $date;
        }

        if (Auth::check()) {
            return Carbon::createFromFormat('Y-m-d H:i:s', $date)->copy()->tz(Auth::user()->iana_tz)->isoFormat('L LT');
        } else {
            return Carbon::createFromFormat('Y-m-d H:i:s', $date)->copy()->tz('America/New_York')->isoFormat('L LT');
        }
    }
}
