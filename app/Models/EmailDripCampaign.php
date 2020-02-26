<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailDripCampaign extends Model
{
    protected $fillable = [
        'group_id',
        'user_id',
        'name',
        'description',
        'campaign',
        'subcampaigns',
        'subject',
        'from',
        'email_field',
        'email_service_provider_id',
        'template_id',
        'active',
        'emails_per_lead',
        'days_between_emails',
        'last_run_from',
        'last_run_to',
    ];

    protected $casts = [
        'subcampaigns' => 'array',
    ];

    public function emailServiceProvider()
    {
        return $this->belongsTo('App\Models\EmailServiceProvider');
    }

    public function emailDripCampaignFilters()
    {
        return $this->hasMany('App\Models\EmailDripCampaignFilter');
    }

    public function emailDripSends()
    {
        return $this->hasMany('App\Models\EmailDripSend');
    }
}
