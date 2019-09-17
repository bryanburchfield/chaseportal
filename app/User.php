<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use App\Dialer;

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

        $dialers=[];
        $dblist = (array) $this->db;

        if (!empty($this->additional_dbs)) {
            $dblist = array_merge($dblist, explode(',', $this->additional_dbs));
        }

        foreach ($dblist as $db) {
            $dialer = Dialer::where('reporting_db', $db)->pluck('reporting_db','dialer_name')->all();
            array_push($dialers, $dialer);
        }
        return $dialers;


        // if (empty($_SESSION['databases'])) {
        //     $selected = $dblist;
        // } else {
        //     $selected = $_SESSION['databases'];
        // }

        // $sql = 'SELECT dialer_name FROM dialers WHERE reporting_db = ?';
        // $dbarray = [];
        // foreach ($dblist as $db) {
        //     $dialer_name = $this->db->fetchValue($sql, [$db]);
        //     $checked = in_array($db, $selected) ? 1 : 0;
        //     $dbarray[$db] = [
        //         'name' => $dialer_name,
        //         'database' => $db,
        //         'selected' => $checked,
        //     ];
        // }

        // return $dbarray;
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
