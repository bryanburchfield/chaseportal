<?php

namespace App\Models;

class Script extends SqlSrvModel
{
    // set db and actual table name
    protected $connection = 'sqlsrv';
    protected $table = 'Scripts';
    public $timestamps = false;
}
