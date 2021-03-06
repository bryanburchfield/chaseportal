<?php

namespace App\Models;

class AdvancedTableField extends SqlSrvModel
{
    // set actual table name
    protected $table = 'AdvancedTableFields';
    public $timestamps = false;

    public function fieldType()
    {
        return $this->belongsTo('App\Models\FieldType', 'FieldType');
    }

    public function campaigns()
    {
        return $this->hasMany('App\Models\Campaign', 'AdvancedTable');
    }
}
