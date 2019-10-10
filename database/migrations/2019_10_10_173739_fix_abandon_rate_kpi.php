<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixAbandonRateKpi extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('kpis')
            ->where('id', 7)
            ->update([
                'inner_sql' => "SELECT Campaign,
            'Cnt' = 1,
            'Abandoned' = CASE WHEN CallStatus='CR_HANGUP' THEN 1 ELSE 0 END
            FROM [{{db}}].[dbo].[DialingResults]
            WHERE CallType IN (1,11)
            AND Duration > 0
            AND CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD','Inbound')
            AND GroupId = {{:group_id}}
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
