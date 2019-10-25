<?php

namespace App\Traits;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

trait CampaignTraits
{
    // NOTE: parent must use SqlServerTraits TimeTraits

    public function getAllCampaigns($fromDate = null, $toDate = null)
    {
        $sql = '';
        $bind = [];

        if (empty($fromDate) || empty($toDate)) {
            $union = '';
            foreach (array_values(Auth::user()->getDatabaseArray()) as $i => $db) {
                $bind['groupid' . $i] = Auth::user()->group_id;

                $sql .= "$union SELECT CampaignName AS Campaign
                FROM [$db].[dbo].[Campaigns]
                WHERE isActive = 1
                AND GroupId = :groupid$i
                AND CampaignName != ''";

                $union = ' UNION';
            }
        } else {
            // make dates into Carbon objects if not already
            $fromDate = $this->makeCarbon($fromDate);
            $toDate = $this->makeCarbon($toDate);

            $tz = Auth::user()->iana_tz;

            $fromDate = $this->localToUtc($fromDate, $tz);
            $toDate = $this->localToUtc($toDate, $tz);

            // convert to datetime strings
            $startDate = $fromDate->format('Y-m-d H:i:s');
            $endDate = $toDate->format('Y-m-d H:i:s');

            $union = '';
            foreach (array_values(Auth::user()->getDatabaseArray()) as $i => $db) {
                $bind['groupid' . $i] = Auth::user()->group_id;
                $bind['startdate' . $i] = $startDate;
                $bind['enddate' . $i] = $endDate;

                $sql .= "$union SELECT DISTINCT Campaign
                FROM [$db].[dbo].[DialingResults]
                WHERE GroupId = :groupid$i
                AND Campaign != ''
                AND Date >= :startdate$i
                AND Date < :enddate$i";

                $union = ' UNION';
            }
        }

        $results = resultsToList($this->runSql($sql, $bind));

        $results = ['_MANUAL_CALL_' => '_MANUAL_CALL_'] + $results;

        ksort($results, SORT_NATURAL);

        return $results;
    }

    public function getAllSubcampaigns($campaign = null)
    {
        if (empty($campaign)) {
            return [];
        }

        $sql = '';
        $union = '';
        foreach (array_values(Auth::user()->getDatabaseArray()) as $i => $db) {
            $bind['groupid' . $i] = Auth::user()->group_id;
            $bind['campaign' . $i] = $campaign;;

            $sql .=  "$union SELECT DISTINCT Subcampaign
            FROM [$db].[dbo].[Leads]
            WHERE GroupId = :groupid$i
            AND Campaign = :campaign$i
            AND Subcampaign is not null
            AND Subcampaign != ''";

            $union = ' UNION';
        }
        $sql .= " ORDER BY Subcampaign";

        $results = resultsToList($this->runSql($sql, $bind));

        return $results;
    }

    private function makeCarbon($datetime)
    {
        if (!is_a($datetime, 'Illuminate\Support\Carbon')) {
            $datetime = new Carbon($datetime);
        }
        return $datetime;
    }
}
