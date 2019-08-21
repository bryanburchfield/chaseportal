<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    // set table stuff
    protected $connection = 'sqlsrv';
    protected $table = 'Campaigns';
    protected $primaryKey = ['CampaignName', 'GroupId'];
    protected $keyType = 'string';
    public $incrementing = false;
}
