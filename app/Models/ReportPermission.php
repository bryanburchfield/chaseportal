<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ReportPermission extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function getReportNameAttribute($value)
    {
        return Str::snake($value);
    }

    public function setReportNameAttribute($value)
    {
        $this->attributes['report_name'] = Str::snake($value);
    }
}
