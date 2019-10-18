<?php

namespace App;

use App\Traits\Schedulable;
use Illuminate\Database\Eloquent\Model;

class LeadMove extends Model
{
    use Schedulable;

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
