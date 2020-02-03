<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvancedTable extends Model
{
    // set db and actual table name
    protected $connection = 'sqlsrv';
    protected $table = 'AdvancedTables';
    public $timestamps = false;
}
