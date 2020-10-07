<?php

namespace App\Models;

class FieldType extends SqlSrvModel
{
    // set db and actual table name
<<<<<<< HEAD
=======
    protected $connection = 'sqlsrv';
>>>>>>> contacts_playbook
    protected $table = 'FieldTypes';
    public $timestamps = false;
}
