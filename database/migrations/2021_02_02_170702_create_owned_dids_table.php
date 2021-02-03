<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOwnedDidsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('phoneflags')->create('owned_dids', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->dateTime('run_date');
            $table->integer('group_id');
            $table->string('group_name');
            $table->integer('owned_did_count');

            $table->index('run_date', 'group_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('phoneflags')->dropIfExists('owned_dids');
    }
}
