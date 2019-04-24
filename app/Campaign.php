<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Campaign extends Model
{
    // set table stuff
    protected $connection = 'sqlsrv';
    protected $table = 'Campaigns';
    protected $primaryKey = ['CampaignName', 'GroupId'];
    protected $keyType = 'string';
    public $incrementing = false;
}
