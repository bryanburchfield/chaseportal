<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDialersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dialers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->smallInteger('dialer_numb');
            $table->string('dialer_name', 50);
            $table->string('dialer_fqdn', 50);
            $table->string('reporting_db', 50);
        });

        DB::table('dialers')->insert(
            array(
                [
                    'dialer_numb' => 1,
                    'dialer_name' => 'BT01.chasedatacorp.com',
                    'dialer_fqdn' => 'BT01.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-01',
                ],
                [
                    'dialer_numb' => 2,
                    'dialer_name' => 'BT02.chasedatacorp.com',
                    'dialer_fqdn' => 'BT02.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-02',
                ],
                [
                    'dialer_numb' => 3,
                    'dialer_name' => 'BT03.chasedatacorp.com',
                    'dialer_fqdn' => 'BT03.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-03',
                ],
                [
                    'dialer_numb' => 4,
                    'dialer_name' => 'BT04.chasedatacorp.com',
                    'dialer_fqdn' => 'BT04.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-04',
                ],
                [
                    'dialer_numb' => 5,
                    'dialer_name' => 'BT05.chasedatacorp.com',
                    'dialer_fqdn' => 'BT05.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-05',
                ],
                [
                    'dialer_numb' => 6,
                    'dialer_name' => 'BT06.chasedatacorp.com',
                    'dialer_fqdn' => 'BT06.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-06',
                ],
                [
                    'dialer_numb' => 7,
                    'dialer_name' => 'bttemp.chasedatacorp.com',
                    'dialer_fqdn' => 'BT07.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-07',
                ],
                [
                    'dialer_numb' => 8,
                    'dialer_name' => 'BT08.chasedatacorp.com',
                    'dialer_fqdn' => 'BT08.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-08',
                ],
                [
                    'dialer_numb' => 9,
                    'dialer_name' => 'BT09.chasedatacorp.com',
                    'dialer_fqdn' => 'BT09.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-09',
                ],
                [
                    'dialer_numb' => 10,
                    'dialer_name' => 'BT10.chasedatacorp.com',
                    'dialer_fqdn' => 'BT10.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-10',
                ],
                [
                    'dialer_numb' => 11,
                    'dialer_name' => 'BT11.chasedatacorp.com',
                    'dialer_fqdn' => 'BT11.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-11',
                ],
                [
                    'dialer_numb' => 12,
                    'dialer_name' => 'BT12.chasedatacorp.com',
                    'dialer_fqdn' => 'BT12.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-12',
                ],
                [
                    'dialer_numb' => 14,
                    'dialer_name' => 'BT14.chasedatacorp.com',
                    'dialer_fqdn' => 'BT14.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-14',
                ],
                [
                    'dialer_numb' => 15,
                    'dialer_name' => 'bt2dialer.chasedatacorp.com',
                    'dialer_fqdn' => 'BT15.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-15',
                ],
                [
                    'dialer_numb' => 16,
                    'dialer_name' => 'BT16.chasedatacorp.com',
                    'dialer_fqdn' => 'BT16.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-16',
                ],
                [
                    'dialer_numb' => 17,
                    'dialer_name' => 'BT17.chasedatacorp.com',
                    'dialer_fqdn' => 'BT17.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-17',
                ],
                [
                    'dialer_numb' => 18,
                    'dialer_name' => 'BT18.chasedatacorp.com',
                    'dialer_fqdn' => 'BT18.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-18',
                ],
                [
                    'dialer_numb' => 19,
                    'dialer_name' => 'BT19.chasedatacorp.com',
                    'dialer_fqdn' => 'BT19.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-19',
                ],
                [
                    'dialer_numb' => 20,
                    'dialer_name' => 'BT20.chasedatacorp.com',
                    'dialer_fqdn' => 'BT20.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-20',
                ],
                [
                    'dialer_numb' => 21,
                    'dialer_name' => 'BT21.chasedatacorp.com',
                    'dialer_fqdn' => 'BT21.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-21',
                ],
                [
                    'dialer_numb' => 22,
                    'dialer_name' => 'BT22.chasedatacorp.com',
                    'dialer_fqdn' => 'BT22.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-22',
                ],
                [
                    'dialer_numb' => 23,
                    'dialer_name' => 'BT23.chasedatacorp.com',
                    'dialer_fqdn' => 'BT23.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-23',
                ],
                [
                    'dialer_numb' => 24,
                    'dialer_name' => 'BT24.chasedatacorp.com',
                    'dialer_fqdn' => 'BT24.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-24',
                ],
                [
                    'dialer_numb' => 25,
                    'dialer_name' => 'BT25.chasedatacorp.com',
                    'dialer_fqdn' => 'BT25.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-25',
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
        Schema::dropIfExists('dialers');
    }
}
