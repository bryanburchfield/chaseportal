<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TwilioAreaCode extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'areacode';

    protected $fillable = [
        'areacode',
    ];
}
