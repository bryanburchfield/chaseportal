<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends SqlSrvModel
{
    /// set db and actual table name
    protected $connection = 'sqlsrv';
    protected $table = 'Leads';
}
