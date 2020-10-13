<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActiveNumber extends Model
{
    protected $primaryKey = 'phone';
    public $timestamps = false;
    protected $guarded = [];
}
