<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailServiceProvider extends Model
{
    protected $fillable = [
        'group_id',
        'user_id',
        'name',
        'provider',
        'username',
        'password',
    ];
}
