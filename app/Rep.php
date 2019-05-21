<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rep extends Model
{
    /// set db and actual table name
    protected $connection = 'sqlsrv';
    protected $table = 'Reps';
}
