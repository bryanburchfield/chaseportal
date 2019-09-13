<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dialer extends Model
{
    // set db and actual table name
    protected $connection = 'sqlsrv';
    protected $table = 'dialers';
}
