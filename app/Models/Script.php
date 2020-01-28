<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Script extends Model
{
    // set db and actual table name
    protected $connection = 'sqlsrv';
    protected $table = 'Scripts';
    public $timestamps = false;
}
