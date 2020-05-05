<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaybookSmsAction extends Model
{
    protected $fillable = [
        'playbook_action_id',
        'from_number',
        'template_id',
        'sms_per_lead',
        'days_between_sms',
    ];
}
