<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaybookEmailAction extends Model
{
    protected $fillable = [
        'playbook_action_id',
        'email_service_provider_id',
        'template_id',
        'email_field',
        'days_between_emails',
        'emails_per_lead',
        'from',
    ];
}
