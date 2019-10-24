<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use App\Dialer;
use App\Notifications\ChaseResetPasswordNotification;
use App\Notifications\WelcomeNotification;
use Illuminate\Support\Facades\Password;

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

    public function getIanaTzAttribute()
    {
        return windowsToUnixTz($this->tz);
    }

    public function isType($type)
    {
        $type = (array) $type;
        return in_array($this->user_type, $type);
    }

    public function getDatabaseArray()
    {
        $dialers = [];
        $dblist = $this->getDatabaseList();

        foreach ($dblist as $db) {
            $dialer = Dialer::where('reporting_db', $db)->pluck('reporting_db', 'dialer_name')->all();
            if ($dialer) {
                $dialers[key($dialer)] = current($dialer);
            }
        }
        return $dialers;
    }

    public function getDatabaseList()
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

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ChaseResetPasswordNotification($token));
    }

    public function sendWelcomeEmail($user)
    {
        $token = Password::broker()->createToken($user);

        $this->notify(new WelcomeNotification($user, $token));
    }
}
