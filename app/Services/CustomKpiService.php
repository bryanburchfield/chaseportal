<?php

namespace App\Services;

use App\Traits\SqlServerTraits;

class CustomKpiService
{
    use SqlServerTraits;

    public static function group211562()
    {
        $service = new CustomKpiService();
        $service->runGroup211562();
    }

    private function runGroup211562()
    {
        $bind = [
            'group_id1' => 211562,
            'group_id2' => 211562,
            'group_id3' => 211562,
        ];

        $sql = "SET NOCOUNT ON;

USE [PowerV2_Reporting_Dialer-24]

DECLARE @MaxDialingAttempts int;
SET @MaxDialingAttempts = dbo.GetGroupCampaignSetting(:group_id1, '', 'MaxDialingAttempts', 0);

SELECT * INTO #ShiftReport FROM (
	SELECT
		L.Campaign,
		COUNT(L.CallStatus) as Leads,
		0 as AvailableLeads
	FROM [Leads] L WITH(NOLOCK)
	LEFT JOIN [Dispos] DI on DI.id = L.DispositionId
    WHERE L.GroupId = :group_id2
    AND L.Campaign LIKE 'Activations Worklist%'
	AND CallStatus not in ('CR_CNCT/CON_CAD', 'CR_CNCT/CON_PVD')
	GROUP BY L.Campaign
) a;

UPDATE #ShiftReport
	SET AvailableLeads = a.Leads
	FROM (
		SELECT
  		    L.Campaign,
			COUNT(DISTINCT L.id) as Leads
		FROM [Leads] L WITH(NOLOCK)
		LEFT JOIN dialer_DialingSettings ds on ds.GroupId = L.GroupId and ds.Campaign = L.Campaign and ds.Subcampaign = L.Subcampaign
		LEFT JOIN dialer_DialingSettings ds2 on ds2.GroupId = L.GroupId and ds2.Campaign = L.Campaign
        WHERE L.GroupId = :group_id3
        AND L.Campaign LIKE 'Activations Worklist%'
		AND (IsNull(ds.MaxDialingAttempts, IsNull(ds2.MaxDialingAttempts, @MaxDialingAttempts)) <> 0
		AND L.Attempt < IsNull(ds.MaxDialingAttempts, IsNull(ds2.MaxDialingAttempts, @MaxDialingAttempts)))
		AND L.WasDialed = 0
		GROUP BY L.Campaign
	) a
	WHERE #ShiftReport.Campaign = a.Campaign

SELECT
	Campaign,
	Leads,
	AvailableLeads
FROM #ShiftReport
ORDER BY Campaign;";

        $results = $this->runSql($sql, $bind);

        dump($results);
    }
}
