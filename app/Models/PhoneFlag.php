<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhoneFlag extends Model
{
    protected $connection = 'phoneflags';

    public $timestamps = false;
    protected $guarded = [];
}
