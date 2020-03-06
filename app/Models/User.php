<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use App\Notifications\ChaseResetPasswordNotification;
use App\Notifications\WelcomeNotification;
use App\Notifications\WelcomeDemoNotification;
use App\Traits\TimeTraits;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Password;

class User extends Authenticatable
{
    use Notifiable;
    use TimeTraits;

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
        'language_displayed',
        'phone',
        'expiration',
        'feature_message_notifications',
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

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'expires_in',
    ];

    public function getIanaTzAttribute()
    {
        return $this->windowsToUnixTz($this->tz);
    }

    public function getExpiresInAttribute()
    {
        if (!$this->isDemo()) {
            return null;
        }

        $expiration = Carbon::parse($this->expiration);

        return $expiration->longRelativeToNowDiffForHumans(2);
    }

    public function isType($type)
    {
        $type = (array) $type;
        return in_array($this->user_type, $type);
    }

    public function isDemo()
    {
        if ($this->user_type == 'demo' || $this->user_type == 'expired') {
            return true;
        }

        return false;
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

    public function readFeatureMessages()
    {
        return $this->hasMany('App\Models\ReadFeatureMessage');
    }

    public function getFeatureMessages()
    {
        return FeatureMessage::where('created_at', '>', $this->created_at)
            ->where('expires_at', '>', now())
            ->orderBy('id', 'desc')
            ->get();
    }

    public function unreadFeatureMessagesCount()
    {
        return FeatureMessage::where('created_at', '>', $this->created_at)
            ->where('expires_at', '>', now())
            ->where('active', '=', 1)
            ->leftJoin('read_feature_messages', function ($join) {
                $join->on('read_feature_messages.feature_message_id', '=', 'feature_messages.id')
                    ->where('read_feature_messages.user_id', '=', $this->id);
            })
            ->whereNull('user_id')
            ->count();
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

    public function sendWelcomeDemoEmail($user)
    {
        $this->notify(new WelcomeDemoNotification($user));
    }
}
