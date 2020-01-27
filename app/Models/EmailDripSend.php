<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailDripSend extends Model
{
    protected $timestamps = false;

    protected $fillable = [
        'email_drip_campaign_id',
        'lead_id',
        'emailed_at',
    ];

    public function emailDripCampaign()
    {
        return $this->belongsTo('App\Models\EmailDripCampaign');
    }
}
