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
        'subcampaign',
        'email_field',
        'smtp_server_id',
        'template_id',
        'active',
        'emails_per_lead',
        'days_between_emails',
        'last_run_from',
        'last_run_to',
    ];

    public function smtpServer()
    {
        return $this->belongsTo('App\Models\SmtpServer');
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
