<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dialer extends Model
{
    public function users($include_additional = false)
    {
        if ($include_additional) {
            $users =  User::where('db', $this->reporting_db)
                ->orWhere('additional_dbs', $this->reporting_db)
                ->get();
        } else {
            $users = User::where('db', $this->reporting_db)->get();
        }

        return $users;
    }
}
