<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailDripTemplate extends Model
{
    protected $fillable = [
        'group_id',
        'user_id',
        'name',
        'from',
        'subject',
        'body',
    ];
}
