<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use ShiftOneLabs\LaravelCascadeDeletes\CascadesDeletes;

class PlaybookAction extends Model
{
    use SoftDeletes;
    use CascadesDeletes;

    protected $fillable = [
        'group_id',
        'name',
        'campaign',
        'action_type',
    ];

    protected $cascadeDeletes = [
        'playbook_lead_action',
        'playbook_sms_action',
        'playbook_email_action',
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
