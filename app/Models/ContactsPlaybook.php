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
        $this->cleanFiltersAndActions();

        DB::commit();
    }

    public function saveFilters($filters = [])
    {
        $filters = collect(array_values((array) $filters));
        $existing_filters = collect();

        $this->filters->each(function ($contacts_playbook_filter) use (&$existing_filters) {
            $existing_filters->push($contacts_playbook_filter->playbook_filter_id);
        });

        DB::beginTransaction();

        // insert any not already there
        $filters->diff($existing_filters)->each(function ($playbook_filter_id) {
            ContactsPlaybookFilter::create(['contacts_playbook_id' => $this->id, 'playbook_filter_id' => $playbook_filter_id]);
        });

        // delete any not submitted
        $existing_filters->diff($filters)->each(function ($playbook_filter_id) {
            ContactsPlaybookFilter::where('contacts_playbook_id', $this->id)
                ->where('playbook_filter_id', $playbook_filter_id)
                ->delete();
        });

        $this->deactiveIfNeeded();

        DB::commit();
    }

    public function saveActions($actions = [])
    {
        $actions = collect(array_values((array) $actions));
        $existing_actions = collect();

        $this->actions->each(function ($contacts_playbook_action) use (&$existing_actions) {
            $existing_actions->push($contacts_playbook_action->playbook_action_id);
        });

        DB::beginTransaction();

        // insert any not already there
        $actions->diff($existing_actions)->each(function ($playbook_action_id) {
            ContactsPlaybookAction::create(['contacts_playbook_id' => $this->id, 'playbook_action_id' => $playbook_action_id]);
        });

        // delete any not submitted
        $existing_actions->diff($actions)->each(function ($playbook_action_id) {
            ContactsPlaybookAction::where('contacts_playbook_id', $this->id)
                ->where('playbook_action_id', $playbook_action_id)
                ->delete();
        });

        $this->deactiveIfNeeded();

        DB::commit();
    }

    private function cleanFiltersAndActions()
    {
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

        $this->deactiveIfNeeded();
    }

    private function deactiveIfNeeded()
    {
        $this->refresh();

        // Decativate if no filters or no actions
        if ($this->active && !$this->allowActive()) {
            $this->update(['active' => 0]);
        }
    }
}
