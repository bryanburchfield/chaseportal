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

    public static function localToUtc(\DateTime $datetime)
    {
        $db = Auth::user()->db;
        config(['database.connections.sqlsrv.database' => $db]);
        $db = DB::connection('sqlsrv');

        $tz = Auth::user()->tz;
        $dt = $datetime->format('Y-m-d H:i:s');

        $sql = "CAST(:date AS DATETIME) AT TIME ZONE :tz AT TIME ZONE 'UTC' AS UTC";
        $bind = [
            'date' => $dt,
            'tz' => $tz,
        ];

        $result = $db->table('Campaigns')
            ->selectRaw($sql, $bind)
            ->value('UTC');

        return new \DateTime($result);
    }

    public static function UtcToLocal(\DateTime $datetime)
    {
        $db = Auth::user()->db;
        config(['database.connections.sqlsrv.database' => $db]);
        $db = DB::connection('sqlsrv');

        $tz = Auth::user()->tz;
        $dt = $datetime->format('Y-m-d H:i:s');

        $sql = "CAST(:date AS DATETIME) AT TIME ZONE 'UTC' AT TIME ZONE :tz AS local";
        $bind = [
            'date' => $dt,
            'tz' => $tz,
        ];

        $result = $db->table('Campaigns')
            ->selectRaw($sql, $bind)
            ->value('local');

        return new \DateTime($result);
    }
}
