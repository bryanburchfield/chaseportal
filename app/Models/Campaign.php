<?php

namespace App\Models;

use App\Traits\SqlServerTraits;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Campaign extends Model
{
    use SqlServerTraits;

    // set table stuff
    protected $connection = 'sqlsrv';
    protected $table = 'Campaigns';
    protected $primaryKey = ['CampaignName', 'GroupId'];
    protected $keyType = 'string';
    public $incrementing = false;

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);

        if (empty(config('database.connections.sqlsrv.database'))) {
            if (Auth::check()) {
                $db = Auth::user()->db;
            }
            config(['database.connections.sqlsrv.database' => $db]);
        }
    }

    public function advancedTable()
    {
        return $this->belongsTo('App\Models\AdvancedTable', 'AdvancedTable');
    }
}
