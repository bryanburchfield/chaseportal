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
            $table->integer('dialer_numb');
            $table->string('dialer_name', 50);
            $table->string('dialer_fqdn', 50);
            $table->string('reporting_db',50);
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
        Schema::dropIfExists('dialers');
    }
}
