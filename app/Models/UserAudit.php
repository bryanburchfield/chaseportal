<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAudit extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'ip',
        'user_id',
        'email',
        'action',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
