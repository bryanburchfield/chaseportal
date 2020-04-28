<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactsPlaybookFilter extends Model
{
    protected $fillable = [
        'contacts_playbook_id',
        'playbook_filter_id',
    ];

    public function playbook()
    {
        return $this->belongsTo('App\Models\ContactsPlaybook');
    }
}
