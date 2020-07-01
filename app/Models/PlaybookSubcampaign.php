<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use ShiftOneLabs\LaravelCascadeDeletes\CascadesDeletes;

class PlaybookSubcampaign extends Model
{
    use SoftDeletes;
    use CascadesDeletes;

    protected $fillable = [
        'contacts_playbook_id',
        'subcampaign',
    ];
}
