<?php

namespace App\Models;

class AdvancedTable extends SqlSrvModel
{
    // set db and actual table name
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
<<<<<<< HEAD

    public function customFields()
    {
        $custom_fields = [];

        foreach ($this->advancedTableFields as $field) {
            $custom_fields[$field->FieldName] = $field->Description;
        }

        return $custom_fields;
    }
=======
>>>>>>> contacts_playbook
}
