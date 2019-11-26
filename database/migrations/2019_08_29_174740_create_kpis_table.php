<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKpisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kpis', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 50);
            $table->string('description', 500);
            $table->text('query');
            $table->timestamps();
        });

        DB::table('kpis')->insert(
            array(
                [
                    'name' => 'Agent Occupancy',
                    'description' => 'Distinct Count of Reps for Current Day',
                    'query' => "SELECT 'Campaign' = ISNULL(Campaign, 'Total'),
'Peak Agents' = COUNT(DISTINCT Rep)
FROM AgentActivity
WHERE GroupId = :groupid
AND Date >= :fromdate
AND Date < :todate
GROUP BY ROLLUP(Campaign);",
                ],
                [
                    'name' => 'Calls Per Rep',
                    'description' => 'Total Calls Reps have taken in the day (Inbound and Outbound)',
                    'query' => "SELECT DR.Rep AS 'Agent',
'Call Count' = COUNT(DR.CallStatus),
'Sales' = COUNT(CASE WHEN DI.Type = '3' THEN 1 ELSE NULL END)
FROM DialingResults DR
INNER JOIN  Dispos DI ON DI.id = DR.DispositionId AND DI.IsSystem = 0
WHERE DR.GroupId = :groupid
AND DR.Rep != ''
AND DR.Date >= :fromdate
AND DR.Date < :todate
GROUP BY DR.Rep
ORDER BY 'Sales' DESC, DR.Rep",
                ],
                [
                    'name' => 'Campaign Sales',
                    'description' => 'Sales per Campaign',
                    'query' => "SELECT 'Campaign' = ISNULL(DR.Campaign, 'Total'),
'Sales' = COUNT(CASE WHEN DI.Type = '3' THEN 1 ELSE NULL END)
FROM DialingResults DR
INNER JOIN  Dispos DI ON DI.id = DR.DispositionId AND DI.IsSystem = 0
WHERE DR.GroupId = :groupid
AND DR.Date >= :fromdate
AND DR.Date < :todate
GROUP BY ROLLUP(DR.Campaign)",
                ],
                [
                    'name' => 'Sales per Agent',
                    'description' => 'A total count of sales an agent has accrued for the day',
                    'query' => "SELECT 'Agent' = ISNULL(DR.Rep, 'Total'),
'Call Count' = COUNT(DR.CallStatus),
'Sales' = COUNT(CASE WHEN DI.Type = '3' THEN 1 ELSE NULL END)
FROM DialingResults DR
INNER JOIN Dispos DI ON DI.id = DR.DispositionId AND DI.IsSystem = 0
WHERE DR.GroupId = :groupid
AND DR.Rep != ''
AND DR.Date >= :fromdate
AND DR.Date < :todate
GROUP BY ROLLUP(DR.Rep)",
                ],
                [
                    'name' => 'Average Hold Time',
                    'description' => 'A calculation of total hold time divided by number of inbound calls',
                    'query' => "SELECT 'Total Calls' = COUNT(CallStatus),
'Hold Time' = CAST(DATEADD(SECOND, SUM(HoldTime), 0) AS TIME(0)),
'Average Hold Time' = CAST(DATEADD(SECOND, (SUM(HoldTime) / COUNT(CallStatus)), 0) AS TIME(0))
FROM DialingResults DR
WHERE CallType = 1
AND CallStatus NOT IN('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound','TRANSFERRED','PARKED')
AND HoldTime >= 0
AND DR.GroupId = :groupid
AND DR.Date >= :fromdate
AND DR.Date < :todate",
                ],
                [
                    'name' => 'Idle Time',
                    'description' => 'Total amount of time spent in waiting status per agent',
                    'query' => "SELECT 'Agent' = ISNULL(Rep, 'Total'),
'Total Wait' = CAST(DATEADD(SECOND, SUM(Duration),0) AS TIME(0)),
'Average Wait Time' = CAST(DATEADD(SECOND,(SUM(Duration) / COUNT(Rep) ),0) AS TIME(0))
FROM AgentActivity
WHERE Action = 'Waiting'
AND Duration <> 0
AND GroupId = :groupid
AND Date >= :fromdate
AND Date < :todate
GROUP BY ROLLUP(Rep)",
                ],
                [
                    'name' => 'Agent Wrap Up Time',
                    'description' => 'Total amount of time spent in after call work',
                    'query' => "SELECT 'Agent' = ISNULL(Rep, 'Total'),
'Wrap Up Time' = CAST(DATEADD(SECOND, SUM(Duration), 0) AS TIME(0)),
'Average Wrap Up Time' = CAST(DATEADD(SECOND, (SUM(Duration) / COUNT(Rep) ),0) AS TIME(0))
FROM AgentActivity
WHERE Action = 'Disposition'
AND GroupId = :groupid
AND Date >= :fromdate
AND Date < :todate
GROUP BY ROLLUP(Rep)",
                ],
                [
                    'name' => 'Abandon Rate',
                    'description' => 'Percentage of total inbound calls that resulted in a customer hangup',
                    'query' => "SELECT 'Campaign' = ISNULL(Campaign, 'Total'),
'Total Inbound Calls' = COUNT(CallStatus),
'Abandoned Calls' = COUNT(CASE WHEN CallStatus='CR_HANGUP' THEN 1 ELSE NULL END),
'Abandon Rate' = FORMAT(CAST(COUNT(CASE WHEN CallStatus='CR_HANGUP' THEN 1 ELSE NULL END) as decimal (18,2)) / COUNT(CallStatus) , 'P')
FROM DialingResults
WHERE CallType = 1
AND CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound','TRANSFERRED','PARKED')
AND GroupId = :groupid
AND Date >= :fromdate
AND Date < :todate
GROUP BY ROLLUP(Campaign)",
                ],
                [
                    'name' => 'Average Handle Time',
                    'description' => 'Total amount of agent time divided by total amount of handled calls',
                    'query' => "SELECT 'Agent' = ISNULL(Rep, 'Total'),
'Calls' = COUNT(id),
'Call Time' = CAST(DATEADD(SECOND, SUM(CASE WHEN Action IN ('InboundCall','Call','ManualCall') THEN Duration ELSE 0 END), 0) AS TIME(0)),
'Disposition' = CAST(DATEADD(SECOND, SUM(CASE WHEN Action IN ('Disposition') THEN Duration ELSE 0 END), 0) AS TIME(0)),
'Average Handle Time' = CAST(DATEADD(SECOND, (SUM(Duration) / COUNT(Rep)), 0) AS TIME (0))
FROM AgentActivity
WHERE Action IN ('InboundCall','Call','ManualCall','Disposition')
AND Duration > 0
AND GroupId = :groupid
AND Date >= :fromdate
AND Date < :todate
GROUP BY ROLLUP(Rep)",
                ],
                [
                    'name' => 'Service Level',
                    'description' => 'This is a measure of the amount of handled calls divided by the total amount of inbounds calls.  A handled call is a inbound call serviced by an agent that was not on hold in que for more then 20 seconds.',
                    'query' => "SELECT 'Campaign' = ISNULL(Campaign, 'Total'),
'Handled Calls' = COUNT(CASE WHEN HoldTime < 20 AND CallStatus <> 'CR_HANGUP' THEN 1 ELSE NULL END),
'Total Inbound Calls' = COUNT(CallStatus),
'Service Level' = FORMAT(CAST(COUNT(CASE WHEN HoldTime < 20 AND CallStatus <> 'CR_HANGUP' THEN 1 ELSE NULL END) as decimal (18,2)) / COUNT(CallStatus) , 'P')
FROM DialingResults
WHERE CallType = 1
AND CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound','TRANSFERRED','PARKED')
AND GroupId = :groupid
AND Date >= :fromdate
AND Date < :todate
GROUP BY ROLLUP(Campaign)",
                ],
                [
                    'name' => 'Contact Rate',
                    'description' => 'Total calls, contacts, and contact rate by Campaign',
                    'query' => "SELECT 'Campaign' = ISNULL(DR.Campaign, 'Total'),
'Total Calls' = COUNT(DR.CallStatus),
'Contacts' = COUNT(CASE WHEN DI.Type IN (2,3) THEN 1 ELSE null END),
'Contact Rate' = FORMAT(CAST(COUNT(CASE WHEN DI.Type IN (2,3) THEN 1 ELSE null END) as decimal(18,2)) / COUNT(DR.CallStatus), 'P')
FROM DialingResults DR
INNER JOIN Dispos DI ON DI.id = DR.DispositionId
WHERE DR.CallType NOT IN (1,7,8,11)
AND DR.GroupId = :groupid
AND Date >= :fromdate
AND Date < :todate
GROUP BY ROLLUP(DR.Campaign)",
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
        Schema::dropIfExists('kpis');
    }
}
