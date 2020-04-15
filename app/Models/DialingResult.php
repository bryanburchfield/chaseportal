<?php

namespace App\Models;

class DialingResult extends SqlSrvModel
{
    // set db and actual table name
    protected $connection = 'sqlsrv';
    protected $table = 'DialingResults';
}
