<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKpiGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kpi_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('kpi_id');
            $table->integer('group_id');
            $table->boolean('active');
            $table->smallInteger('interval')->nullable();
            $table->timestamps();

            $table->foreign('kpi_id')->references('id')->on('kpis');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kpi_groups');
    }
}
