<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactsPlaybookAction extends Model
{
    protected $fillable = [
        'contacts_playbook_id',
        'playbook_action_id',
    ];

    public function contacts_playbook()
    {
        return $this->belongsTo('App\Models\ContactsPlaybook');
    }

    public function playbook_action()
    {
        return $this->belongsTo('App\Models\PlaybookAction');
    }
}
