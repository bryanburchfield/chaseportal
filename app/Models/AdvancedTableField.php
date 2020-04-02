<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AdvancedTableField extends Model
{
    // set db and actual table name
    protected $connection = 'sqlsrv';
    protected $table = 'AdvancedTableFields';
    public $timestamps = false;

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

    public function fieldType()
    {
        return $this->belongsTo('App\Models\FieldType', 'FieldType');
    }
}
