<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Schedulable;

class PhoneFlag extends Model
{
    protected $primaryKey = 'phone';
    protected $guarded = [];
}
