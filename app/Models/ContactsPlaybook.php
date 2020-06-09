<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use ShiftOneLabs\LaravelCascadeDeletes\CascadesDeletes;

class ContactsPlaybook extends Model
{
    use SoftDeletes;
    use CascadesDeletes;

    protected $fillable = [
        'group_id',
        'name',
        'campaign',
        'subcampaign',
        'active',
    ];

    protected $cascadeDeletes = ['playbook_touches'];

    public function playbook_touches()
    {
        return $this->hasMany('App\Models\PlaybookTouch');
    }

    public function playbook_runs()
    {
        return $this->hasMany('App\Models\PlaybookRun');
    }

    public function allowActive()
    {
        $this->refresh();

        return $this->playbook_touches->where('active', 1)->count() > 0;
    }
}
