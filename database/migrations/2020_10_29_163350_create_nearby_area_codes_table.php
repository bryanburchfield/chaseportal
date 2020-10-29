<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class CreateNearbyAreaCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nearby_area_codes', function (Blueprint $table) {
            $table->smallInteger('source_npa');
            $table->smallInteger('nearby_npa');

            $table->primary(['source_npa', 'nearby_npa']);
        });

        Artisan::call('db:seed', [
            '--class' => 'NearbyAreaCodeSeeder',
            '--force' => true
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nearby_area_codes');
    }
}
