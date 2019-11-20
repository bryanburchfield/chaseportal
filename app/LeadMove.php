<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LeadMove extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'batch_id',
        'lead_rule_id',
        'reversed',
    ];

    public function leadRule()
    {
        return $this->belongsTo('App\LeadRule');
    }

    public function leadMoveDetails()
    {
        return $this->hasMany('App\LeadMoveDetail');
    }
}
