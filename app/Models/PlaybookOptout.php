<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaybookOptout extends Model
{
    protected $fillable = [
        'group_id',
        'email',
    ];
}
