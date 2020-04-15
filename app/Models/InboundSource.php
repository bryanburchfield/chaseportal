<?php

namespace App\Models;

class InboundSource extends SqlSrvModel
{
    // set db and actual table name
    protected $connection = 'sqlsrv';
    protected $table = 'InboundSources';
}
