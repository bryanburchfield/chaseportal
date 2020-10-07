<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SqlSrvModel extends Model
{
    protected $connection = 'sqlsrv';

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);

        // Set db based on logged in user if not already set
        if (empty(config('database.connections.sqlsrv.database'))) {
            if (Auth::check()) {
                config(['database.connections.sqlsrv.database' => Auth::user()->db]);
            }
        }
    }
}
