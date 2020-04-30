<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaybookAction extends Model
{
    protected $fillable = [
        'group_id',
        'name',
        'campaign',
        'action_type',
    ];

    public function contacts_playbook_actions()
    {
        return $this->hasMany('App\Models\ContactsPlaybookAction');
    }
}
