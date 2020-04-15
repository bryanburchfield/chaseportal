<?php

namespace App\Models;

class AdvancedTable extends SqlSrvModel
{
    // set db and actual table name
    protected $connection = 'sqlsrv';
    protected $table = 'AdvancedTables';
    public $timestamps = false;

    public function campaigns()
    {
        return $this->hasMany('App\Models\Campaign', 'AdvancedTable');
    }

    public function advancedTableFields()
    {
        return $this->hasMany('App\Models\AdvancedTableField', 'AdvancedTable');
    }
}
