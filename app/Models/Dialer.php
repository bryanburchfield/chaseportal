<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dialer extends Model
{
    public function users($include_additional = false)
    {
        if ($include_additional) {
            $users =  User::where(
                function ($query) {
                    $query->where('db', $this->reporting_db)
                        ->orWhere('additional_dbs', $this->reporting_db);
                }
            )
                ->whereNotIn('user_type', ['demo', 'expired'])
                ->get();
        } else {
            $users = User::where('db', $this->reporting_db)
                ->whereNotIn('user_type', ['demo', 'expired'])
                ->get();
        }

        return $users;
    }

    public function group_count($include_additional = false)
    {
        if ($include_additional) {
            $count = User::where(
                function ($query) {
                    $query->where('db', $this->reporting_db)
                        ->orWhere('additional_dbs', $this->reporting_db);
                }
            )
                ->whereNotIn('user_type', ['demo', 'expired'])
                ->distinct('group_id')
                ->count();
        } else {
            $count = User::where('db', $this->reporting_db)
                ->whereNotIn('user_type', ['demo', 'expired'])
                ->distinct('group_id')
                ->count();
        }

        return $count;
    }
}
