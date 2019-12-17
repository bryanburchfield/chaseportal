<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadRule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'rule_name',
        'source_campaign',
        'source_subcampaign',
        'destination_campaign',
        'destination_subcampaign',
        'description',
        'active'
    ];

    public function leadMoves()
    {
        return $this->hasMany('App\Models\LeadMove');
    }

    public function leadRuleFilters()
    {
        return $this->hasMany('App\Models\LeadRuleFilter');
    }
}
