<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LeadMove extends Model
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lead_rule_id',
        'reporting_db',
        'lead_id',
        'run_date',
        'succeeded',
    ];

    public function leadRule()
    {
        return $this->belongsTo('App\LeadRule');
    }
}
