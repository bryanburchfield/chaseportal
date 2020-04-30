<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ContactsPlaybook extends Model
{
    protected $fillable = [
        'group_id',
        'name',
        'campaign',
        'subcampaign',
        'active',
    ];

    public function filters()
    {
        return $this->hasMany('App\Models\ContactsPlaybookFilter');
    }

    public function actions()
    {
        return $this->hasMany('App\Models\ContactsPlaybookAction');
    }

    public function allowActive()
    {
        return $this->actions->count() > 0
            && $this->filters->count() > 0;
    }

    public function update(array $attributes = [], array $options = [])
    {
        DB::beginTransaction();

        parent::update($attributes, $options);

        // remove any filters and actions that don't match the campaign
        foreach ($this->filters as $filter) {
            if ($filter->playbook_filter->campaign !== null && $filter->playbook_filter->campaign !== $this->campaign) {
                $filter->delete();
            }
        }
        foreach ($this->actions as $action) {
            if ($action->playbook_action->campaign !== null && $action->playbook_action->campaign !== $this->campaign) {
                $action->delete();
            }
        }

        DB::commit();
    }
}
