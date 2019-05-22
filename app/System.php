<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class System extends Model
{
    // set db and actual table name
    protected $connection = 'sqlsrv';
    protected $table = 'sys.time_zone_info';
}
