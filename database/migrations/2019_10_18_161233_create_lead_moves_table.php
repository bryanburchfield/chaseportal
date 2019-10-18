<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadMovesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lead_moves', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('lead_rule_id');
            $table->string('reporting_db', 50);
            $table->bigInteger('lead_id');
            $table->date('run_date');
            $table->boolean('succeeded');
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
    }
}
