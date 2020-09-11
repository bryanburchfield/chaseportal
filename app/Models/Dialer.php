<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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
                ->where('password', '!=', 'SSO');
        } else {
            $users = User::where('db', $this->reporting_db)
                ->whereNotIn('user_type', ['demo', 'expired'])
                ->where('password', '!=', 'SSO');
        }

        if (Auth::User()->isType('superadmin')) {
            return $users->get();
        } else {
            return $users->where('group_id', Auth::User()->group_id)->get();
        }
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
                ->where('password', '!=', 'SSO')
                ->distinct('group_id')
                ->count();
        } else {
            $count = User::where('db', $this->reporting_db)
                ->whereNotIn('user_type', ['demo', 'expired'])
                ->where('password', '!=', 'SSO')
                ->distinct('group_id')
                ->count();
        }

        return $count;
    }
}
