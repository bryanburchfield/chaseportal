<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;

class Script extends SqlSrvModel
{
    // set db and actual table name
    protected $connection = 'sqlsrv';
    protected $table = 'Scripts';
    public $timestamps = false;

    public static function emailTemplates($group_id = null)
    {
        if (Auth::check() && empty($group_id)) {
            $group_id = Auth::user()->group_id;
        }

        return Script::where('GroupId', $group_id)
            ->where('Name', 'like', 'email[_]%')
            ->whereNotNull('HtmlContent')
            ->where('HtmlContent', '!=', '')
            ->get();
    }
}
