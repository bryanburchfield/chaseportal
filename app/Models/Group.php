<?php

namespace App\Models;

class Group extends SqlSrvModel
{
    // set db and actual table name
    protected $connection = 'sqlsrv';
    protected $table = 'Groups';
    public $timestamps = false;

    public static function allGroups()
    {
        return self::where('GroupId', '>', -1)
            ->where('IsActive', 1)
            ->orderBy('GroupName')
            ->get();
    }
}
