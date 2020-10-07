<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SqlSrvModel extends Model
{
<<<<<<< HEAD
    protected $connection = 'sqlsrv';

=======
>>>>>>> contacts_playbook
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);

        // Set db based on logged in user if not already set
        if (empty(config('database.connections.sqlsrv.database'))) {
            if (Auth::check()) {
<<<<<<< HEAD
                config(['database.connections.sqlsrv.database' => Auth::user()->db]);
=======
                $db = Auth::user()->db;
                config(['database.connections.sqlsrv.database' => $db]);
>>>>>>> contacts_playbook
            }
        }
    }
}
