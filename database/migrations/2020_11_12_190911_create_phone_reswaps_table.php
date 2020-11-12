<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhoneReswapsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('phoneflags')->create('phone_reswaps', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->dateTime('run_date');
            $table->integer('group_id');
            $table->smallInteger('dialer_numb');
            $table->string('phone', 15);
            $table->string('replaced_by', 15);
            $table->string('replaced_again', 15);

            $table->index('run_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('phoneflags')->dropIfExists('phone_reswaps');
    }
}
