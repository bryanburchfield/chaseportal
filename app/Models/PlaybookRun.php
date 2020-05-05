<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaybookRun extends Model
{
    protected $fillable = [
        'contacts_playbook_id',
    ];

    public function contacts_playbook()
    {
        return $this->belongsTo('App\Models\ContactsPlaybook');
    }

    public function playbook_run_details()
    {
        return $this->hasMany('App\Models\PlaybookRunDetail');
    }
}
