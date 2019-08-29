<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'tz',
        'db',
        'user_type',
        'group_id',
        'additional_dbs',
        'app_token',
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

    public function persistFilters(Request $request)
    {
        if ($request->has('campaign')) {
            $val = json_encode(['campaign' => $request->input('campaign')]);
            $this->persist_filters = $val;
            $this->save();
        }
    }

    public function isMultiDb()
    {
        return !empty($this->additional_dbs);
    }
}
