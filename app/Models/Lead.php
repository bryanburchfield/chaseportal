<?php

namespace App\Models;

use App\Traits\SqlServerTraits;

class Lead extends SqlSrvModel
{
    use SqlServerTraits;

    protected $table = 'Leads';

    public function allFields()
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

        $sql = "SELECT TOP 1 * FROM $tabname WHERE LeadId = '" . $this->IdGuid . "'";

        $results = $this->runSql($sql);

        // Bail if no record found
        if (empty($results)) {
            return $lead_array;
        }

        // Build array
        foreach ($campaign->advancedTable->customFields() as $k => $v) {
            $v = empty($v) ? $k : $v;

            if (!empty($results[0][$k])) {
                $lead_array['ExtraFields'][$v] = $results[0][$k];
            }
        }

        return $lead_array;
    }
}
