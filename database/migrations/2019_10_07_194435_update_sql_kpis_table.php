<?php

use Illuminate\Database\Migrations\Migration;

class UpdateSqlKpisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('kpis')
            ->where('id', 4)
            ->update([
                'inner_sql' => "SELECT 'Cnt' = COUNT(CallStatus),
'HoldTime' = SUM(HoldTime)
FROM [{{db}}].[dbo].[DialingResults] DR
WHERE CallType IN (1,11)
AND CallStatus NOT IN('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound','TRANSFERRED','PARKED')
AND HoldTime >= 0
AND DR.GroupId = {{:group_id}}
AND DR.Date >= {{:fromdate}}
AND DR.Date < {{:todate}}",
            ]);

        DB::table('kpis')
            ->where('id', 7)
            ->update([
                'inner_sql' => "SELECT Campaign,
'Cnt' = 1,
'Abandoned' = CASE WHEN CallStatus='CR_HANGUP' THEN 1 ELSE 0 END
FROM [{{db}}].[dbo].[DialingResults]
WHERE CallType IN (1,11)
AND DR.Duration > 0
AND CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound')
AND GroupId = {{:group_id}}
AND Date >= {{:fromdate}}
AND Date < {{:todate}}"
            ]);

        DB::table('kpis')
            ->where('id', 10)
            ->update([
                'inner_sql' => "SELECT Campaign,
'Cnt' = 1,
'Contacts' = CASE WHEN DI.Type > 1 THEN 1 ELSE 0 END
FROM [{{db}}].[dbo].[DialingResults] DR
CROSS APPLY (SELECT TOP 1 [Type]
    FROM [{{db}}].[dbo].[Dispos]
    WHERE Disposition = DR.CallStatus
    AND (GroupId = DR.GroupId OR IsSystem=1)
    AND (Campaign = DR.Campaign OR Campaign = '')
    ORDER BY [Description] Desc) DI
WHERE DR.CallType NOT IN (1,7,8,11)
AND DR.Rep != ''
AND Duration > 0
AND DR.CallStatus NOT IN (
'CR_CEPT', 'CR_CNCT/CON_PAMD', 'CR_NOANS',
'CR_NORB', 'CR_BUSY', 'CR_DROPPED', 'CR_FAXTONE',
'CR_FAILED', 'CR_DISCONNECTED', 'CR_CNCT/CON_CAD',
'CR_CNCT/CON_PVD', '', 'CR_HANGUP', 'Inbound')
AND DR.GroupId = {{:group_id}}
AND Date >= {{:fromdate}}
AND Date < {{:todate}}",
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
