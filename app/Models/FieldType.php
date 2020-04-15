<?php

namespace App\Models;

class FieldType extends SqlSrvModel
{
    // set db and actual table name
    protected $connection = 'sqlsrv';
    protected $table = 'FieldTypes';
    public $timestamps = false;
}
