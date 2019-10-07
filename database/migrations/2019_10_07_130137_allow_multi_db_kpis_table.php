<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AllowMultiDbKpisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Delete all kpis and related records
        Schema::disableForeignKeyConstraints();
        DB::table('kpi_recipients')->truncate();
        DB::table('kpi_groups')->truncate();
        DB::table('kpis')->truncate();
        Schema::enableForeignKeyConstraints();

        Schema::table('kpis', function (Blueprint $table) {
            $table->dropColumn('query');
            $table->text('outer_sql');
            $table->text('inner_sql');
            $table->boolean('union_all');
        });

        DB::table('kpis')->insert(
            array(
                [
                    'name' => 'Agent Occupancy',
                    'description' => 'Distinct Count of Reps for Current Day',
                    'outer_sql' => "SELECT
'Campaign' = ISNULL(Campaign, 'Total'),
'Peak Agents' = COUNT(DISTINCT Rep)
FROM (
{{inner_sql}}
) tmp
GROUP BY ROLLUP(Campaign);",
                    'inner_sql' => "SELECT Campaign, Rep
FROM [{{db}}].[dbo].[AgentActivity]
WHERE GroupId = {{:group_id}}
AND Date >= {{:fromdate}}
AND Date < {{:todate}}
AND Campaign != ''",
                    'union_all' => true,
                ],

                [
                    'name' => 'Calls & Sales Per Rep',
                    'description' => 'Total Calls taken % Sales made by Reps (Inbound and Outbound)',
                    'outer_sql' => "SELECT Rep AS 'Agent',
'Call Count' = SUM(Cnt),
'Sales' = SUM(Sales)
FROM (
{{inner_sql}}
) tmp
GROUP BY Rep
ORDER BY 'Sales' DESC, Rep;",
                    'inner_sql' => "SELECT DR.Rep,
'Cnt' = COUNT(DR.CallStatus),
'Sales' = SUM(CASE WHEN DI.Type = '3' THEN 1 ELSE 0 END)
FROM [{{db}}].[dbo].[DialingResults] DR
OUTER APPLY (SELECT TOP 1 [Type]
    FROM  [{{db}}].[dbo].[Dispos]
    WHERE Disposition = DR.CallStatus
    AND (GroupId = DR.GroupId OR IsSystem = 1)
    AND (Campaign = DR.Campaign OR Campaign = '')
    ORDER BY [Description] Desc) DI
WHERE DR.GroupId = {{:group_id}}
AND DR.Rep != ''
AND DR.CallStatus NOT IN (
'CR_CEPT', 'CR_CNCT/CON_PAMD', 'CR_NOANS',
'CR_NORB', 'CR_BUSY', 'CR_DROPPED', 'CR_FAXTONE',
'CR_FAILED', 'CR_DISCONNECTED', 'CR_CNCT/CON_CAD',
'CR_CNCT/CON_PVD', ' ', 'CR_HANGUP', 'Inbound')
AND DR.Date >= {{:fromdate}}
AND DR.Date < {{:todate}}
GROUP BY DR.Rep",
                    'union_all' => true,
                ],

                [
                    'name' => 'Campaign Sales',
                    'description' => 'Sales per Campaign',
                    'outer_sql' => "SELECT 'Campaign' = ISNULL(Campaign, 'Total'),
'Sales' = SUM(Sales)
FROM (
{{inner_sql}}
) tmp
GROUP BY ROLLUP(Campaign);",
                    'inner_sql' => "SELECT DR.Campaign,
'Sales' = SUM(CASE WHEN DI.Type = '3' THEN 1 ELSE 0 END)
FROM [{{db}}].[dbo].[DialingResults] DR
OUTER APPLY (SELECT TOP 1 [Type]
    FROM  [{{db}}].[dbo].[Dispos]
    WHERE Disposition = DR.CallStatus
    AND (GroupId = DR.GroupId OR IsSystem = 1)
    AND (Campaign = DR.Campaign OR Campaign = '')
    ORDER BY [Description] Desc) DI
WHERE DR.GroupId = {{:group_id}}
AND DR.CallStatus NOT IN (
'CR_CEPT', 'CR_CNCT/CON_PAMD', 'CR_NOANS',
'CR_NORB', 'CR_BUSY', 'CR_DROPPED', 'CR_FAXTONE',
'CR_FAILED', 'CR_DISCONNECTED', 'CR_CNCT/CON_CAD',
'CR_CNCT/CON_PVD', ' ', 'CR_HANGUP', 'Inbound')
AND DR.Campaign != ''
AND DR.Date >= {{:fromdate}}
AND DR.Date < {{:todate}}
GROUP BY DR.Campaign",
                    'union_all' => true,
                ],

                [
                    'name' => 'Average Hold Time',
                    'description' => 'A calculation of total hold time divided by number of inbound calls',
                    'outer_sql' => "SELECT 'Total Calls' = SUM(Cnt),
'Hold Time' = CAST(DATEADD(SECOND, SUM(HoldTime), 0) AS TIME(0)),
'Average Hold Time' = CAST(DATEADD(SECOND, (SUM(HoldTime) / SUM(Cnt)), 0) AS TIME(0))
FROM (
{{inner_sql}}
) tmp;",
                    'inner_sql' => "SELECT 'Cnt' = COUNT(CallStatus),
'HoldTime' = SUM(HoldTime)
FROM [{{db}}].[dbo].[DialingResults] DR
WHERE CallType = 1
AND CallStatus NOT IN('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound','TRANSFERRED','PARKED')
AND HoldTime >= 0
AND DR.GroupId = {{:group_id}}
AND DR.Date >= {{:fromdate}}
AND DR.Date < {{:todate}}",
                    'union_all' => true,
                ],

                [
                    'name' => 'Idle Time',
                    'description' => 'Total amount of time spent in waiting status per agent',
                    'outer_sql' => "SELECT 'Agent' = ISNULL(Rep, 'Total'),
'Total Wait' = CAST(DATEADD(SECOND, SUM(Duration),0) AS TIME(0)),
'Average Wait Time' = CAST(DATEADD(SECOND,(SUM(Duration) / SUM(Cnt) ),0) AS TIME(0))
FROM (
{{inner_sql}}
) tmp
GROUP BY ROLLUP(Rep);",
                    'inner_sql' => "SELECT AA.Rep, SUM(AA.Duration) as Duration, COUNT(AA.id) as Cnt
FROM [{{db}}].[dbo].[AgentActivity] AA
WHERE AA.Action = 'Waiting'
AND AA.Duration > 0
AND AA.GroupId = {{:group_id}}
AND AA.Date >= {{:fromdate}}
AND AA.Date < {{:todate}}
GROUP BY AA.Rep",
                    'union_all' => true,
                ],

                [
                    'name' => 'Agent Wrap Up Time',
                    'description' => 'Total amount of time spent in after call work',
                    'outer_sql' => "SELECT 'Agent' = ISNULL(Rep, 'Total'),
'Wrap Up Time' = CAST(DATEADD(SECOND, SUM(Duration), 0) AS TIME(0)),
'Average Wrap Up Time' = CAST(DATEADD(SECOND, (SUM(Duration) / COUNT(Rep) ),0) AS TIME(0))
FROM (
{{inner_sql}}
) tmp
GROUP BY ROLLUP(Rep);",
                    'inner_sql' => "SELECT Rep, Duration
FROM [{{db}}].[dbo].[AgentActivity]
WHERE Action = 'Disposition'
AND GroupId = {{:group_id}}
AND Date >= {{:fromdate}}
AND Date < {{:todate}}",
                    'union_all' => true,
                ],

                [
                    'name' => 'Abandon Rate',
                    'description' => 'Percentage of total inbound calls that resulted in a customer hangup',
                    'outer_sql' => "SELECT 'Campaign' = ISNULL(Campaign, 'Total'),
'Total Inbound Calls' = SUM(Cnt),
'Abandoned Calls' = SUM(Abandoned),
'Abandon Rate' = FORMAT(CAST(SUM(Abandoned) as decimal (18,2)) / SUM(Cnt) , 'P')
FROM (
{{inner_sql}}
) tmp
GROUP BY ROLLUP(Campaign);",
                    'inner_sql' => "SELECT Campaign,
'Cnt' = 1,
'Abandoned' = CASE WHEN CallStatus='CR_HANGUP' THEN 1 ELSE 0 END
FROM [{{db}}].[dbo].[DialingResults]
WHERE CallType = 1
AND CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound','TRANSFERRED','PARKED')
AND GroupId = {{:group_id}}
AND Date >= {{:fromdate}}
AND Date < {{:todate}}",
                    'union_all' => true,
                ],

                [
                    'name' => 'Average Handle Time',
                    'description' => 'Total amount of agent time divided by total amount of handled calls',
                    'outer_sql' => "SELECT 'Agent' = ISNULL(Rep, 'Total'),
'Calls' = SUM(Calls),
'Call-Time' = CAST(DATEADD(SECOND, SUM(CallTime), 0) AS TIME(0)),
'Disposition' = CAST(DATEADD(SECOND, SUM(DispositionTime), 0) AS TIME(0)),
'Average Handle Time' = CAST(DATEADD(SECOND, (SUM(Duration) / SUM(Calls)), 0) AS TIME (0))
FROM (
{{inner_sql}}
) tmp
GROUP BY ROLLUP(Rep);",
                    'inner_sql' => "SELECT Rep, Duration,
'Calls' = 1,
'CallTime' = CASE WHEN Action IN ('InboundCall','Call','ManualCall') THEN Duration ELSE 0 END,
'DispositionTime' = CASE WHEN Action IN ('Disposition') THEN Duration ELSE 0 END
FROM [{{db}}].[dbo].[AgentActivity]
WHERE Action IN ('InboundCall','Call','ManualCall','Disposition')
AND Duration > 0
AND GroupId = {{:group_id}}
AND Date >= {{:fromdate}}
AND Date < {{:todate}}",
                    'union_all' => true,
                ],

                [
                    'name' => 'Service Level',
                    'description' => 'This is a measure of the amount of handled calls divided by the total amount of inbounds calls.  A handled call is a inbound call serviced by an agent that was not on hold in que for more then 20 seconds.',
                    'outer_sql' => "SELECT 'Campaign' = ISNULL(Campaign, 'Total'),
'Handled Calls' = SUM(Handled),
'Total Inbound Calls' = SUM(Cnt),
'Service Level' = FORMAT(CAST(SUM(Handled) as decimal (18,2)) / SUM(Cnt) , 'P')
FROM (
{{inner_sql}}
) tmp
GROUP BY ROLLUP(Campaign);",
                    'inner_sql' => "SELECT Campaign,
'Handled' = CASE WHEN HoldTime < 20 AND CallStatus <> 'CR_HANGUP' THEN 1 ELSE 0 END,
'Cnt' = 1
FROM [{{db}}].[dbo].[DialingResults]
WHERE CallType = 1
AND CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound','TRANSFERRED','PARKED')
AND GroupId = {{:group_id}}
AND Date >= {{:fromdate}}
AND Date < {{:todate}}",
                    'union_all' => true,
                ],

                [
                    'name' => 'Contact Rate',
                    'description' => 'Total calls, contacts, and contact rate by Campaign',
                    'outer_sql' => "SELECT 'Campaign' = ISNULL(Campaign, 'Total'),
'Total Calls' = SUM(Cnt),
'Contacts' = SUM(Contacts),
'Contact Rate' = FORMAT(CAST(SUM(Contacts) as decimal(18,2)) / SUM(Cnt), 'P')
FROM (
{{inner_sql}}
) tmp
GROUP BY ROLLUP(Campaign);",
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
AND DR.GroupId = {{:group_id}}
AND Date >= {{:fromdate}}
AND Date < {{:todate}}",
                    'union_all' => true,
                ],
            )
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kpis', function (Blueprint $table) {
            $table->dropColumn('outer_sql');
            $table->dropColumn('inner_sql');
            $table->dropColumn('union_all');
            $table->text('query');
        });
    }
}
