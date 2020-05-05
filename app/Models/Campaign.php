<?php

namespace App\Models;

use App\Traits\SqlServerTraits;

class Campaign extends SqlSrvModel
{
    // set table stuff
    protected $connection = 'sqlsrv';
    protected $table = 'Campaigns';
    protected $primaryKey = ['CampaignName', 'GroupId'];
    protected $keyType = 'string';
    public $incrementing = false;

    use SqlServerTraits;

    public function advancedTable()
    {
        return $this->belongsTo('App\Models\AdvancedTable', 'AdvancedTable');
    }

    public function getFilterFields()
    {
        $fields = $this->defaultLeadFields();

        if ($this->id) {

            if ($this->advancedTable) {
                foreach ($this->advancedTable->advancedTableFields as $field) {
                    $fields[$field->FieldName] = $field->fieldType->Type;
                }
            }
        }

        return $fields;
    }
}
