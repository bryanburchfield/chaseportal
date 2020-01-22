<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmtpServer extends Model
{
    protected $fillable = [
        'group_id',
        'user_id',
        'name',
        'host',
        'port',
        'username',
        'password',
    ];
}
