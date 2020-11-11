<?php

namespace App\Models;

use App\Traits\SqlServerTraits;
use Illuminate\Support\Str;

class Lead extends SqlSrvModel
{
    use SqlServerTraits;

    protected $table = 'Leads';

    protected $fillable = [
        'FirstName',
        'LastName',
        'Address',
        'City',
        'State',
        'ZipCode',
        'PrimaryPhone',
        'SecondaryPhone',
        'Rep',
        'CallStatus',
        'Campaign',
        'Attempt',
        'Notes',
        'Subcampaign',
        'FullName',
    ];

    public function filledFields()
    {
        $lead_array = $this->toArray();
        $lead_array['ExtraFields'] = [];

        $campaign = Campaign::where('CampaignName', $this->Campaign)
            ->where('GroupId', $this->GroupId)
            ->first();

        // Make sure we found a campaign
        if (!$campaign) {
            return $lead_array;
        }

        // If campaign has no advanced table, bail here
        if (!$campaign->advancedTable) {
            return $lead_array;
        }

        $tabname = 'ADVANCED_' . $campaign->advancedTable->TableName;

        $sql = "SELECT TOP 1 * FROM [$tabname] WHERE LeadId = '" . $this->IdGuid . "'";

        $results = $this->runSql($sql);

        // Bail if no record found
        if (empty($results)) {
            return $lead_array;
        }

        // Build array
        foreach ($results[0] as $k => $v) {
            $v = empty($v) ? $k : $v;

            if (!empty($results[0][$k])) {
                $lead_array['ExtraFields'][$v] = $results[0][$k];
            }
        }

        return $lead_array;
    }

    public function customFields()
    {
        $extra_fields = [];

        $campaign = Campaign::where('CampaignName', $this->Campaign)
            ->where('GroupId', $this->GroupId)
            ->first();

        // Make sure we found a campaign
        if (!$campaign) {
            return $extra_fields;
        }

        // If campaign has no advanced table, bail here
        if (!$campaign->advancedTable) {
            return $extra_fields;
        }

        $tabname = 'ADVANCED_' . $campaign->advancedTable->TableName;

        $sql = "SELECT TOP 1 * FROM [$tabname] WHERE LeadId = '" . $this->IdGuid . "'";

        $results = $this->runSql($sql);

        // Bail if no record found
        if (empty($results)) {
            return $extra_fields;
        }

        $descriptions = [];
        // Build descriptions
        foreach ($campaign->advancedTable->customFields() as $k => $v) {
            $k = Str::studly($k);
            $descriptions[$k] = empty($v) ? $k : $v;
        }

        // Build array
        foreach ($results[0] as $k => $v) {
            // ignore LeadId
            if ($k == 'LeadId') {
                continue;
            }

            $extra_fields[$k] = [
                'key' => $k,
                'description' => $descriptions[Str::studly($k)],
                'value' => $v,
            ];
        }

        return $extra_fields;
    }
}
