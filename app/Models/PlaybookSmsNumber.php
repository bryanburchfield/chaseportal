<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaybookSmsNumber extends Model
{
    protected $fillable = [
        'group_id',
        'from_number',
    ];
}
