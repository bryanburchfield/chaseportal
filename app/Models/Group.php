<?php

namespace App\Models;

class Group extends SqlSrvModel
{
    // set db and actual table name
    protected $table = 'Groups';
    protected $primaryKey = 'GroupId';
    public $timestamps = false;

    public static function allGroups()
    {
        return self::where('GroupId', '>', -1)
            ->where('IsActive', 1)
            ->orderBy('GroupName')
            ->get();
    }
}
