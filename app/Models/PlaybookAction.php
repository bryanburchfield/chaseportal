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

    public function playbook_touch_actions()
    {
        return $this->hasMany('App\Models\PlaybookTouchAction');
    }

    public function playbook_lead_action()
    {
        return $this->hasOne('App\Models\PlaybookLeadAction');
    }

    public function playbook_email_action()
    {
        return $this->hasOne('App\Models\PlaybookEmailAction');
    }

    public function playbook_sms_action()
    {
        return $this->hasOne('App\Models\PlaybookSmsAction');
    }
}
