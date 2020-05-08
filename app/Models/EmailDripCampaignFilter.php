<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailDripCampaignFilter extends Model
{
    protected $fillable = [
        'email_drip_campaign_id',
        'field',
        'operator',
        'value',
    ];

    public function emailDripCampaign()
    {
        return $this->belongsTo('App\Models\EmailDripCampaign');
    }
}
