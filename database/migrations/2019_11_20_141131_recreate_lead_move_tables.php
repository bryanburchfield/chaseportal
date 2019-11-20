<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RecreateLeadMoveTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('lead_moves');

        Schema::create('lead_moves', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('lead_rule_id');
            $table->boolean('reversed')->default(false);
            $table->timestamps();
        });

        Schema::create('lead_move_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('lead_move_id');
            $table->string('reporting_db', 50);
            $table->bigInteger('lead_id');
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
        Schema::dropIfExists('lead_moves');
        Schema::dropIfExists('lead_move_details');
    }
}
