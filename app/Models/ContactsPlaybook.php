<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactsPlaybook extends Model
{
    protected $fillable = [
        'group_id',
        'name',
        'campaign',
        'subcampaign',
        'active',
    ];

    public function filters()
    {
        return $this->hasMany('App\Models\ContactsPlaybookFilter');
    }

    public function actions()
    {
        return $this->hasMany('App\Models\ContactsPlaybookAction');
    }
}
