<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsFromNumber extends Model
{
    protected $fillable = [
        'group_id',
        'from_number',
    ];

    protected $cascadeDeletes = ['playbook_sms_actions'];

    public function playbook_sms_actions()
    {
        return $this->hasMany('App\Models\PlaybookSmsAction');
    }
}
