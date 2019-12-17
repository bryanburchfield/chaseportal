<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DncFile extends Model
{
    protected $fillable = [
        'group_id',
        'user_id',
        'description',
        'uploaded_at',
        'processed_at',
    ];

    public function dncFileDetails()
    {
        return $this->hasMany('App\Models\DncFileDetail');
    }
}
