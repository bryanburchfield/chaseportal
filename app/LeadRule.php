<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadRule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'rule_name',
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
