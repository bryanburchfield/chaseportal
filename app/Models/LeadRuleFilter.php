<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadRuleFilter extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'lead_rule_id',
        'type',
        'value',
    ];

    public function leadRule()
    {
        return $this->belongsTo('App\Models\LeadRule');
    }
}
