<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use ShiftOneLabs\LaravelCascadeDeletes\CascadesDeletes;

class PlaybookTouch extends Model
{
    use SoftDeletes;
    use CascadesDeletes;

    protected $fillable = [
        'contacts_playbook_id',
        'name',
        'active',
    ];

    protected $cascadeDeletes = ['playbook_touch_actions', 'playbook_touch_filters'];

    public function contacts_playbook()
    {
        return $this->belongsTo('App\Models\ContactsPlaybook');
    }

    public function playbook_touch_filters()
    {
        return $this->hasMany('App\Models\PlaybookTouchFilter');
    }

    public function playbook_touch_actions()
    {
        return $this->hasMany('App\Models\PlaybookTouchAction');
    }

    public function activate()
    {
        if ($this->allowActive()) {
            $this->active = 1;
            $this->save();

            return true;
        }

        return false;
    }

    public function deactivate()
    {
        $this->active = 0;
        $this->save();

        if ($this->contacts_playbook->active && !$this->contacts_playbook->allowActive()) {
            $this->contacts_playbook->active = 0;
            $this->contacts_playbook->save();
        }

        return true;
    }

    public function allowActive()
    {
        $this->refresh();

        return $this->playbook_touch_filters->count() > 0
            && $this->playbook_touch_actions->count() > 0;
    }

    public function save(array $attributes = [], array $options = [])
    {
        DB::beginTransaction();

        parent::save($attributes, $options);
        $this->cleanFiltersAndActions();

        DB::commit();
    }

    public function update(array $attributes = [], array $options = [])
    {
        DB::beginTransaction();

        parent::update($attributes, $options);
        $this->cleanFiltersAndActions();

        // Might have deactivated the PB, so save it too.
        $this->contacts_playbook->save();

        DB::commit();
    }

    public function saveFilters($filters = [])
    {
        $filters = collect(array_values((array) $filters));
        $existing_filters = collect();

        $this->playbook_touch_filters->each(function ($playbook_touch_filter) use (&$existing_filters) {
            $existing_filters->push($playbook_touch_filter->playbook_filter_id);
        });

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
    }

    public function saveActions($actions = [])
    {
        $actions = collect(array_values((array) $actions));
        $existing_actions = collect();

        $this->refresh();

        $this->playbook_touch_actions->each(function ($playbook_touch_action) use ($existing_actions) {
            $existing_actions->push($playbook_touch_action->playbook_action_id);
        });

        // insert any not already there
        $actions->diff($existing_actions)->each(function ($playbook_action_id) use ($existing_actions) {
            PlaybookTouchAction::create(['playbook_touch_id' => $this->id, 'playbook_action_id' => $playbook_action_id]);
            $existing_actions->push($playbook_action_id);
        });

        // delete any not submitted
        $existing_actions->diff($actions)->each(function ($playbook_action_id) {
            PlaybookTouchAction::where('playbook_touch_id', $this->id)
                ->where('playbook_action_id', $playbook_action_id)
                ->delete();
        });

        $this->deactiveIfNeeded();
    }

    public function cleanFiltersAndActions()
    {
        // get adv table of playbook
        $playbook_table = '';
        $playbook_campaign = Campaign::where('GroupId', Auth::user()->group_id)
            ->where('CampaignName', $this->contacts_playbook->campaign)
            ->first();

        if ($playbook_campaign) {
            if ($playbook_campaign->advancedTable) {
                $playbook_table = $playbook_campaign->advancedTable->TableName;
            }
        }

        // remove any filters that don't match the advanced table
        foreach ($this->playbook_touch_filters as $playbook_touch_filter) {
            // if both null that's fine
            if ($playbook_touch_filter->playbook_filter->campaign === null && $this->contacts_playbook->campaign === null) {
                continue;
            }

            // get adv table of filter
            $filter_table = '';
            $campaign = Campaign::where('GroupId', Auth::user()->group_id)
                ->where('CampaignName', $playbook_touch_filter->playbook_filter->campaign)
                ->first();

            if ($campaign) {
                if ($campaign->advancedTable) {
                    $filter_table = $campaign->advancedTable->TableName;
                }
            }

            // blank filter table works for all
            if ($filter_table == '') {
                continue;
            }

            // if they don't match, delete the playbook_touch_filter
            if ($filter_table != $playbook_table) {
                $playbook_touch_filter->delete();
            }
        }

        // remove any actions that don't match the advanced table
        foreach ($this->playbook_touch_actions as $playbook_touch_action) {
            // if both null that's fine
            if ($playbook_touch_action->playbook_action->campaign === null && $this->contacts_playbook->campaign === null) {
                continue;
            }

            // get adv table of action
            $action_table = '';
            $campaign = Campaign::where('GroupId', Auth::user()->group_id)
                ->where('CampaignName', $playbook_touch_action->playbook_action->campaign)
                ->first();

            if ($campaign) {
                if ($campaign->advancedTable) {
                    $action_table = $campaign->advancedTable->TableName;
                }

                // blank action table works for all
                if ($action_table == '') {
                    continue;
                }

                // if they don't match, delete the playbook_touch_action
                if ($action_table != $playbook_table) {
                    $playbook_touch_action->delete();
                }
            }
        }

        $this->deactiveIfNeeded();
    }

    private function deactiveIfNeeded()
    {
        $this->refresh();

        // Decativate if no filters or no actions
        if ($this->active && !$this->allowActive()) {
            $this->deactivate();
        }
    }
}
