<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaybookEmailAction extends Model
{
    protected $fillable = [
        'playbook_action_id',
        'email_service_provider_id',
        'subject',
        'from',
        'email_field',
        'template_id',
        'emails_per_lead',
        'days_between_emails',
    ];
}
