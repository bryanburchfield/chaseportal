<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DncFile extends Model
{
    protected $fillable = [
        'group_id',
        'user_id',
        'filename',
        'description',
        'uploaded_at',
        'process_started_at',
        'processed_at',
        'reverse_started_at',
        'reversed_at',
    ];

    protected $hidden = [
        'dncFileDetails',
    ];

    public function dncFileDetails()
    {
        return $this->hasMany('App\Models\DncFileDetail');
    }

    public function errorRecs()
    {
        return DncFileDetail::where('dnc_file_id', $this->id)
            ->whereNotNull('succeeded')
            ->where('succeeded', false);
    }
}
