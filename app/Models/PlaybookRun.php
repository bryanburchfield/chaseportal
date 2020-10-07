<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class PlaybookRun extends Model
{
    protected $fillable = [
        'contacts_playbook_id',
    ];

    public function contacts_playbook()
    {
        return $this->belongsTo('App\Models\ContactsPlaybook');
    }

    public function playbook_run_touches()
    {
        return $this->hasMany('App\Models\PlaybookRunTouch');
    }

    public function record_count()
    {
        $count = 0;
        foreach ($this->playbook_run_touches as $playbook_run_touch) {
            $count += $playbook_run_touch->record_count();
        }

        return $count;
    }

    public function getCreatedAtAttribute($date)
    {
        if (Auth::check()) {
            return Carbon::createFromFormat('Y-m-d H:i:s', $date)->copy()->tz(Auth::user()->iana_tz)->isoFormat('L LT');
        } else {
            return Carbon::createFromFormat('Y-m-d H:i:s', $date)->copy()->tz('America/New_York')->isoFormat('L LT');
        }
    }
}
