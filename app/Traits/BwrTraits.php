<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait BwrTraits
{
    private function getAllDataSourcePrimary()
    {
        $db = Auth::User()->db;

        $sql = '';

        $sql .=  "SELECT DISTINCT Data_Source_Primary
            FROM [$db].[dbo].[ADVANCED_BWR_Master_Table]
            WHERE Data_Source_Primary is not null
            AND Data_Source_Primary != ''";

        $results = resultsToList($this->runSql($sql));

        ksort($results, SORT_NATURAL | SORT_FLAG_CASE);

        return $results;
    }

    private function getAllDataSourceSecondary()
    {
        $db = Auth::User()->db;

        $sql = '';

        $sql .=  "SELECT DISTINCT Data_Source_Secondary
            FROM [$db].[dbo].[ADVANCED_BWR_Master_Table]
            WHERE Data_Source_Secondary is not null
            AND Data_Source_Secondary != ''";

        $results = resultsToList($this->runSql($sql));

        ksort($results, SORT_NATURAL | SORT_FLAG_CASE);

        return $results;
    }

    private function getAllProgram()
    {
        $db = Auth::User()->db;

        $sql = '';

        $sql .=  "SELECT DISTINCT Program
            FROM [$db].[dbo].[ADVANCED_BWR_Master_Table]
            WHERE Program is not null
            AND Program != ''";

        $results = resultsToList($this->runSql($sql));

        ksort($results, SORT_NATURAL | SORT_FLAG_CASE);

        return $results;
    }
}
