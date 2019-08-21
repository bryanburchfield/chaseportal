<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'tz', 'db', 'user_type', 'group_id', 'additional_db', 'app_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function type($type)
    {
        $type = (array) $type;
        return in_array($this->user_type, $type);
    }

    public function getDatabaseArray()
    {
        $dblist = (array) $this->db;

        if (!empty($this->additional_dbs)) {
            $dblist = array_merge($dblist, explode(',', $this->additional_dbs));
        }
        return $dblist;
    }
}
