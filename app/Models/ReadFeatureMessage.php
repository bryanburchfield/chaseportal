<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReadFeatureMessage extends Model
{

	public $timestamps = false;
	
    protected $fillable = [
        'message_id',
        'user_id',
        'read_at',
    ];
}
