<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use ShiftOneLabs\LaravelCascadeDeletes\CascadesDeletes;

class PlaybookCampaign extends Model
{
    use SoftDeletes;
    use CascadesDeletes;

    protected $fillable = [
        'contacts_playbook_id',
        'campaign',
    ];
}
