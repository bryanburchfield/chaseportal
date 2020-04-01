<?php

namespace App\Models;

use App\Traits\SqlServerTraits;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use SqlServerTraits;

    // set table stuff
    protected $connection = 'sqlsrv';
    protected $table = 'Campaigns';
    protected $primaryKey = ['CampaignName', 'GroupId'];
    protected $keyType = 'string';
    protected $fillable = [
        'CampaignName',
        'GroupId',
    ];

    public $incrementing = false;

    /**
     * Return the Custom Table ID tied to a dialer campaign
     * 
     * @param mixed $campaign 
     * @return int|mixed 
     */
    public function customTableId()
    {
        $sql = "SELECT AdvancedTable
            FROM Campaigns
            WHERE GroupId = :group_id
            AND CampaignName = :campaign";

        $bind = [
            'group_id' => $this->GroupId,
            'campaign' => $this->CampaignName,
        ];

        $results = $this->runSql($sql, $bind);

        if (!isset($results[0]['AdvancedTable'])) {
            return -1;
        }

        return $results[0]['AdvancedTable'];
    }

    /**
     * Return all string fields of the Custom Table tied to a campaign
     * 
     * @param Request $request 
     * @return array|mixed 
     */
    public function customTableFields()
    {
        $table_id = $this->customTableId();

        if ($table_id == -1) {
            return [];
        }

        $sql = "SELECT FieldName, [Type]
            FROM AdvancedTableFields
            INNER JOIN FieldTypes ON FieldTypes.id = AdvancedTableFields.FieldType
            WHERE AdvancedTable = :table_id";

        $results = resultsToList($this->runSql($sql, ['table_id' => $table_id]));

        return $results;
    }
}
