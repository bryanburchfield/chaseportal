<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlaybookEmailAction extends Model
{
    use SoftDeletes;

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
