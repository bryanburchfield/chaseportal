<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InboundSource extends Model
{
    // set db and actual table name
    protected $connection = 'sqlsrv';
    protected $table = 'InboundSources';
}
