<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use ShiftOneLabs\LaravelCascadeDeletes\CascadesDeletes;

class ContactsPlaybook extends Model
{
    use SoftDeletes;
    use CascadesDeletes;

    protected $fillable = [
        'group_id',
        'name',
        'campaign',
        'last_run_from',
        'last_run_to',
        'active',
    ];

    protected $cascadeDeletes = [
        'playbook_touches',
        'playbook_campaigns',
        'playbook_subcampaigns',
    ];

    public function playbook_campaigns()
    {
        return $this->hasMany('App\Models\PlaybookCampaign');
    }

    public function playbook_subcampaigns()
    {
        return $this->hasMany('App\Models\PlaybookSubcampaign');
    }

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

    public static function create(array $attributes = [])
    {
        DB::beginTransaction();

        $contacts_playbook = static::query()->create($attributes);

        if (!isset($attributes['campaigns'])) {
            $attributes['campaigns'] = [];
        }

        if (!isset($attributes['subcampaigns'])) {
            $attributes['subcampaigns'] = [];
        }

        $contacts_playbook->saveCampaigns($attributes['campaigns']);
        $contacts_playbook->saveSubcampaigns($attributes['subcampaigns']);

        DB::commit();

        return $contacts_playbook;
    }

    public function update(array $attributes = [], array $options = [])
    {
        DB::beginTransaction();

        parent::update($attributes, $options);

        if (!isset($attributes['subcampaigns'])) {
            $attributes['subcampaigns'] = [];
        }

        if (!isset($attributes['campaigns'])) {
            $attributes['campaigns'] = [];
        }

        $this->saveCampaigns($attributes['campaigns']);
        $this->saveSubcampaigns($attributes['subcampaigns']);

        DB::commit();
    }

    public function saveCampaigns($campaigns = [])
    {
        $campaigns = collect(array_values((array) $campaigns));
        $existing_campaigns = collect();

        $this->playbook_campaigns->each(function ($playbook_campaign) use (&$existing_campaigns) {
            $existing_campaigns->push($playbook_campaign->campaign);
        });

        // insert any not already there
        $campaigns->diff($existing_campaigns)->each(function ($campaign) {
            PlaybookCampaign::create(['contacts_playbook_id' => $this->id, 'campaign' => $campaign]);
        });

        // delete any not submitted
        $existing_campaigns->diff($campaigns)->each(function ($campaign) {
            PlaybookCampaign::where('contacts_playbook_id', $this->id)
                ->where('campaign', $campaign)
                ->delete();
        });
    }

    public function saveSubcampaigns($subcampaigns = [])
    {
        $subcampaigns = collect(array_values((array) $subcampaigns));
        $existing_subcampaigns = collect();

        $this->playbook_subcampaigns->each(function ($playbook_subcampaign) use (&$existing_subcampaigns) {
            $existing_subcampaigns->push($playbook_subcampaign->subcampaign);
        });

        // insert any not already there
        $subcampaigns->diff($existing_subcampaigns)->each(function ($subcampaign) {
            PlaybookSubcampaign::create(['contacts_playbook_id' => $this->id, 'subcampaign' => $subcampaign]);
        });

        // delete any not submitted
        $existing_subcampaigns->diff($subcampaigns)->each(function ($subcampaign) {
            PlaybookSubcampaign::where('contacts_playbook_id', $this->id)
                ->where('subcampaign', $subcampaign)
                ->delete();
        });
    }
}
