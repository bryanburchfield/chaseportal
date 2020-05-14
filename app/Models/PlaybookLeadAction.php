<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaybookLeadAction extends Model
{
    protected $fillable = [
        'playbook_action_id',
        'to_campaign',
        'to_subcampaign',
        'to_callstatus',
    ];
}
