<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaybookSmsAction extends Model
{
    protected $fillable = [
        'playbook_action_id',
        'from_number',
        'message',
    ];
}
