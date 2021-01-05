<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;

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
        // Hide some fields from non-admins
        $callinfo = 1;

        if (Auth::check()) {
            if (Auth::user()->isType(['admin', 'superadmin'])) {
                $callinfo = 0;
            }
        }

        return $this->hasMany('App\Models\AdvancedTableField', 'AdvancedTable')
            ->where('InCallInfo', '>=', $callinfo)
            ->orderBy('FieldOrder', 'desc')
            ->orderBy('FieldName');
    }

    public function customFields()
    {
        $custom_fields = [];

        foreach ($this->advancedTableFields as $field) {
            $custom_fields[$field->FieldName] = $field->Description;
        }

        return $custom_fields;
    }
}
