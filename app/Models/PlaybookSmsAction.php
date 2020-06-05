<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaybookSmsAction extends Model
{
    protected $fillable = [
        'playbook_action_id',
        'sms_from_number_id',
        'template_id',
        'sms_per_lead',
        'days_between_sms',
    ];

    public function sms_from_number()
    {
        return $this->belongsTo('App\Models\SmsFromNumber');
    }
}
