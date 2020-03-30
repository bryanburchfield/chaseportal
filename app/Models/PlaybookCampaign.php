<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaybookCampaign extends Model
{
    protected $fillable = [
        'group_id',
        'name',
        'campaign',
        'subcampaign',
        'active',
    ];
}
