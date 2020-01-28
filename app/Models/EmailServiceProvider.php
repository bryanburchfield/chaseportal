<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailServiceProvider extends Model
{
    protected $fillable = [
        'group_id',
        'user_id',
        'name',
        'provider_type',
        'properties',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function emailDripCampaigns()
    {
        return $this->hasMany('App\Models\EmailDripCampaign');
    }
}
