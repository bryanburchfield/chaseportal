<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDbLeadmovesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('db_leadmoves', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('lead_id');
            $table->smallInteger('db_from');
            $table->smallInteger('db_to');
            $table->string('process_id', 15)->nullable();
            $table->boolean('succeeded')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('db_leadmoves');
    }
}
