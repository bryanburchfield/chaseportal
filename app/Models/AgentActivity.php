<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentActivity extends Model
{
    // set db and actual table name
    protected $connection = 'sqlsrv';
    protected $table = 'AgentActivity';
}
