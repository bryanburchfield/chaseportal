<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadMoveDetail extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'lead_move_id',
        'reporting_db',
        'lead_id',
        'succeeded',
    ];

    public function leadMove()
    {
        return $this->belongsTo('App\Models\LeadMove');
    }
}
