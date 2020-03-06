<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TwilioNumber extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'phone';

    protected $fillable = [
        'phone',
    ];
}
