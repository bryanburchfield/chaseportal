<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaybookCampaignFilter extends Model
{
    protected $fillable = [
        'playbook_campaign_id',
        'playbook_filter_id',
    ];
}
