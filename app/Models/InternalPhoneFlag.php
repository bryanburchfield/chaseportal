<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class InternalPhoneFlag extends Model
{
    protected $connection = 'phoneflags';

    public $timestamps = false;
    protected $guarded = [];

    public function setSwapErrorAttribute($value)
    {
        $this->attributes['swap_error'] = Str::limit($value, 190, '');
    }
}
