<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlaybookLeadAction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'playbook_action_id',
        'to_campaign',
        'to_subcampaign',
        'to_callstatus',
    ];
}
