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

    public function getFilterFields($include_calculated = false)
    {
        $fields = [];
        if ($include_calculated) {
            $fields = $this->calculatedLeadFields();
        }
        $fields += $this->defaultLeadFields();

        if ($this->id) {
            if ($this->advancedTable) {
                foreach ($this->advancedTable->advancedTableFields as $field) {
                    $fields[$field->FieldName] = $field->fieldType->Type;
                }
            }
        }

        return $fields;
    }

    private function calculatedLeadFields()
    {
        return [
            'Lead Age' => 'integer',
            'Attempts' => 'integer',
            'Days Called' => 'integer',
            'Ring Group' => 'string',
            'Call Status' => 'string',
        ];
    }

    private function defaultLeadFields()
    {
        return [
            'ClientId' => 'string',
            'FirstName' => 'string',
            'LastName' => 'string',
            'PrimaryPhone' => 'phone',
            'Address' => 'string',
            'City' => 'string',
            'State' => 'string',
            'ZipCode' => 'string',
            'Notes' => 'text',
            'Campaign' => 'string',
            'Subcampaign' => 'string',
        ];
    }
}
