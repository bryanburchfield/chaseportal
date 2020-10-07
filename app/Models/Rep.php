<?php

namespace App\Models;

class Rep extends SqlSrvModel
{
    /// set db and actual table name
    protected $connection = 'sqlsrv';
    protected $table = 'Reps';
}
