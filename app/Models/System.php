<?php

namespace App\Models;

class System extends SqlSrvModel
{
    // set db and actual table name
    protected $connection = 'sqlsrv';
    protected $table = 'sys.time_zone_info';
}
