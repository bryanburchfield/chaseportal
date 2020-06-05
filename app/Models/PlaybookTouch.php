<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ContactsPlaybook extends Model
{
    protected $fillable = [
        'contacts_playbook_id',
        'name',
        'active',
    ];

    public function playbook_touch_filters()
    {
        return $this->hasMany('App\Models\PlaybookTouchFilter');
    }

    public function playbook_touch_actions()
    {
        return $this->hasMany('App\Models\PlaybookTouchAction');
    }

    public function allowActive()
    {
        $this->refresh();

        return $this->playbook_touch_filters->count() > 0
            && $this->playbook_touch_actions->count() > 0;
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

        $this->playbook_touch_filters->each(function ($playbook_touch_filter) use (&$existing_filters) {
            $existing_filters->push($playbook_touch_filter->playbook_filter_id);
        });

        DB::beginTransaction();

        // insert any not already there
        $filters->diff($existing_filters)->each(function ($playbook_filter_id) {
            PlaybookTouchFilter::create(['playbook_touch_id' => $this->id, 'playbook_filter_id' => $playbook_filter_id]);
        });

        // delete any not submitted
        $existing_filters->diff($filters)->each(function ($playbook_filter_id) {
            PlaybookTouchFilter::where('playbook_touch_id', $this->id)
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

        $this->playbook_touch_actions->each(function ($playbook_touch_action) use (&$existing_actions) {
            $existing_actions->push($playbook_touch_action->playbook_action_id);
        });

        DB::beginTransaction();

        // insert any not already there
        $actions->diff($existing_actions)->each(function ($playbook_action_id) {
            PlaybookTouchAction::create(['playbook_touch_id' => $this->id, 'playbook_action_id' => $playbook_action_id]);
        });

        // delete any not submitted
        $existing_actions->diff($actions)->each(function ($playbook_action_id) {
            PlaybookTouchAction::where('playbook_touch_id', $this->id)
                ->where('playbook_action_id', $playbook_action_id)
                ->delete();
        });

        $this->deactiveIfNeeded();

        DB::commit();
    }

    private function cleanFiltersAndActions()
    {
        // remove any filters and actions that don't match the campaign
        foreach ($this->playbook_touch_filters as $playbook_touch_filter) {
            if ($playbook_touch_filter->playbook_filter->campaign !== null && $playbook_touch_filter->playbook_filter->campaign !== $this->campaign) {
                $playbook_touch_filter->delete();
            }
        }
        foreach ($this->playbook_touch_actions as $playbook_touch_action) {
            if ($playbook_touch_action->playbook_action->campaign !== null && $playbook_touch_action->playbook_action->campaign !== $this->campaign) {
                $playbook_touch_action->delete();
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
