<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InsertLeadCountKpi extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('kpis')->insert(
            [
                'name' => 'lead_count',
                'created_at' => now(),
                'updated_at' => now(),
                'outer_sql' => '{{inner_sql}}',
                'inner_sql' => "SET NOCOUNT ON;

DECLARE @MaxDialingAttempts int;
SET @MaxDialingAttempts = dbo.GetGroupCampaignSetting({{:group_id}}, '', 'MaxDialingAttempts', 0);

SELECT * INTO #ShiftReport FROM (
	SELECT
		isNull(L.Campaign, '') as Campaign,
		isNull(L.Subcampaign, '') as Subcampaign,
		COUNT(L.CallStatus) as Leads,
		0 as AvailableLeads
	FROM [{{db}}].[dbo].[Leads] L WITH(NOLOCK)
	LEFT JOIN  [{{db}}].[dbo].[Dispos] DI on DI.id = L.DispositionId
	WHERE L.GroupId = {{:group_id1}}
	AND CallStatus not in ('CR_CNCT/CON_CAD', 'CR_CNCT/CON_PVD')
	GROUP BY L.Campaign, L.Subcampaign
) a;

UPDATE #ShiftReport
	SET AvailableLeads = a.Leads
	FROM (
		SELECT
			l.Campaign,
			l.Subcampaign,
			COUNT(DISTINCT l.id) as Leads
		FROM [{{db}}].[dbo].[Leads] l WITH(NOLOCK)
		LEFT JOIN dialer_DialingSettings ds on ds.GroupId = l.GroupId and ds.Campaign = l.Campaign and ds.Subcampaign = l.Subcampaign
		LEFT JOIN dialer_DialingSettings ds2 on ds2.GroupId = l.GroupId and ds2.Campaign = l.Campaign
		WHERE l.GroupId = {{:group_id2}}
		AND (IsNull(ds.MaxDialingAttempts, IsNull(ds2.MaxDialingAttempts, @MaxDialingAttempts)) <> 0
		AND l.Attempt < IsNull(ds.MaxDialingAttempts, IsNull(ds2.MaxDialingAttempts, @MaxDialingAttempts)))
		AND l.WasDialed = 0
		GROUP BY l.Campaign, l.Subcampaign
	) a
	WHERE #ShiftReport.Campaign = a.Campaign
	AND #ShiftReport.Subcampaign = a.Subcampaign;

SELECT
	Campaign,
	Subcampaign,
	Leads,
	AvailableLeads
FROM #ShiftReport
ORDER BY Campaign, Subcampaign;",
                'union_all' => 1
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('kpis')->where('name', 'lead_count')->delete();
    }
}
