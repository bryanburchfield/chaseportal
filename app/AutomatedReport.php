<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AutomatedReport extends Model
{
	public $timestamps = false;
    protected $fillable= ['user_id','report'];
}
