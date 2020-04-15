<?php

namespace App\Models;

use App\Traits\SqlServerTraits;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Campaign extends SqlSrvModel
{
    // set table stuff
    protected $connection = 'sqlsrv';
    protected $table = 'Campaigns';
    protected $primaryKey = ['CampaignName', 'GroupId'];
    protected $keyType = 'string';
    public $incrementing = false;

    public function advancedTable()
    {
        return $this->belongsTo('App\Models\AdvancedTable', 'AdvancedTable');
    }
}
