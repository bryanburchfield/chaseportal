<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dialer extends Model
{
    public function clientCount()
    {
        return User::where('db', $this->reporting_db)->count();
    }
}
