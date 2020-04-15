<?php

namespace App\Models;

class Lead extends SqlSrvModel
{
    /// set db and actual table name
    protected $connection = 'sqlsrv';
    protected $table = 'Leads';
}
