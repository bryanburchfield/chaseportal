<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCalleridResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('callerid_results', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('client', 50);
            $table->string('ip', 15);
            $table->string('phone', 11);
            $table->string('raw_phone', 20);
            $table->string('result');
            $table->timestamps();

            $table->index('phone');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('callerid_results');
    }
}
