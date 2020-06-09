<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use ShiftOneLabs\LaravelCascadeDeletes\CascadesDeletes;

class SmsFromNumber extends Model
{
    use SoftDeletes;
    use CascadesDeletes;

    protected $fillable = [
        'group_id',
        'from_number',
    ];

    protected $cascadeDeletes = ['playbook_sms_actions'];

    public function playbook_sms_actions()
    {
        return $this->hasMany('App\Models\PlaybookSmsAction');
    }
}
