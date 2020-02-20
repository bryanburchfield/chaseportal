<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PauseCode extends Model
{
    protected $fillable = [
        'group_id',
        'user_id',
        'code',
        'minutes_per_day',
        'times_per_day',
    ];
}
