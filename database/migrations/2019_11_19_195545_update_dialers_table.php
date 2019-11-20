<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDialersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('dialers');

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
                    'dialer_name' => 'dialer-01.chasedatacorp.com',
                    'dialer_fqdn' => 'dialer-01.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-01',
                ],
                [
                    'dialer_numb' => 2,
                    'dialer_name' => 'dialer-02.chasedatacorp.com',
                    'dialer_fqdn' => 'dialer-02.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-02',
                ],
                [
                    'dialer_numb' => 3,
                    'dialer_name' => 'dialer-03.chasedatacorp.com',
                    'dialer_fqdn' => 'dialer-03.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-03',
                ],
                [
                    'dialer_numb' => 4,
                    'dialer_name' => 'dialer-04.chasedatacorp.com',
                    'dialer_fqdn' => 'dialer-04.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-04',
                ],
                [
                    'dialer_numb' => 5,
                    'dialer_name' => 'dialer-05.chasedatacorp.com',
                    'dialer_fqdn' => 'dialer-05.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-05',
                ],
                [
                    'dialer_numb' => 6,
                    'dialer_name' => 'dialer-06.chasedatacorp.com',
                    'dialer_fqdn' => 'dialer-06.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-06',
                ],
                [
                    'dialer_numb' => 7,
                    'dialer_name' => 'bttemp.chasedatacorp.com',
                    'dialer_fqdn' => 'dialer-07.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-07',
                ],
                [
                    'dialer_numb' => 8,
                    'dialer_name' => 'dialer-08.chasedatacorp.com',
                    'dialer_fqdn' => 'dialer-08.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-08',
                ],
                [
                    'dialer_numb' => 9,
                    'dialer_name' => 'dialer-09.chasedatacorp.com',
                    'dialer_fqdn' => 'dialer-09.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-09',
                ],
                [
                    'dialer_numb' => 10,
                    'dialer_name' => 'dialer-10.chasedatacorp.com',
                    'dialer_fqdn' => 'dialer-10.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-10',
                ],
                [
                    'dialer_numb' => 11,
                    'dialer_name' => 'dialer-11.chasedatacorp.com',
                    'dialer_fqdn' => 'dialer-11.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-11',
                ],
                [
                    'dialer_numb' => 12,
                    'dialer_name' => 'dialer-12.chasedatacorp.com',
                    'dialer_fqdn' => 'dialer-12.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-12',
                ],
                [
                    'dialer_numb' => 14,
                    'dialer_name' => 'dialer-14.chasedatacorp.com',
                    'dialer_fqdn' => 'dialer-14.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-14',
                ],
                [
                    'dialer_numb' => 15,
                    'dialer_name' => 'bt2dialer.chasedatacorp.com',
                    'dialer_fqdn' => 'dialer-15.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-15',
                ],
                [
                    'dialer_numb' => 16,
                    'dialer_name' => 'dialer-16.chasedatacorp.com',
                    'dialer_fqdn' => 'dialer-16.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-16',
                ],
                [
                    'dialer_numb' => 17,
                    'dialer_name' => 'dialer-17.chasedatacorp.com',
                    'dialer_fqdn' => 'dialer-17.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-17',
                ],
                [
                    'dialer_numb' => 18,
                    'dialer_name' => 'dialer-18.chasedatacorp.com',
                    'dialer_fqdn' => 'dialer-18.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-18',
                ],
                [
                    'dialer_numb' => 19,
                    'dialer_name' => 'dialer-19.chasedatacorp.com',
                    'dialer_fqdn' => 'dialer-19.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-19',
                ],
                [
                    'dialer_numb' => 20,
                    'dialer_name' => 'dialer-20.chasedatacorp.com',
                    'dialer_fqdn' => 'dialer-20.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-20',
                ],
                [
                    'dialer_numb' => 21,
                    'dialer_name' => 'dialer-21.chasedatacorp.com',
                    'dialer_fqdn' => 'dialer-21.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-21',
                ],
                [
                    'dialer_numb' => 22,
                    'dialer_name' => 'dialer-22.chasedatacorp.com',
                    'dialer_fqdn' => 'dialer-22.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-22',
                ],
                [
                    'dialer_numb' => 23,
                    'dialer_name' => 'dialer-23.chasedatacorp.com',
                    'dialer_fqdn' => 'dialer-23.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-23',
                ],
                [
                    'dialer_numb' => 24,
                    'dialer_name' => 'dialer-24.chasedatacorp.com',
                    'dialer_fqdn' => 'dialer-24.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-24',
                ],
                [
                    'dialer_numb' => 25,
                    'dialer_name' => 'dialer-25.chasedatacorp.com',
                    'dialer_fqdn' => 'dialer-25.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-25',
                ],
                [
                    'dialer_numb' => 26,
                    'dialer_name' => 'dialer-26.chasedatacorp.com',
                    'dialer_fqdn' => 'dialer-26.chasedatacorp.com',
                    'reporting_db' => 'PowerV2_Reporting_Dialer-26',
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
        //  There is no way to roll this back
    }
}
