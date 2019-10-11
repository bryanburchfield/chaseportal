<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LeadRule extends Model
{
    protected $fillable = [
        'source_campaign',
        'source_subcampaign',
        'filter_type',
        'filter_value',
        'destination_campaign',
        'destination_subcampaign',
        'description',
        'active'
    ];
}
