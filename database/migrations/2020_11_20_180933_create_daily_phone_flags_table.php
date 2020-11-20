<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyPhoneFlagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('phoneflags')->create('daily_phone_flags', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('call_date');
            $table->integer('group_id');
            $table->string('group_name');
            $table->smallInteger('dialer_numb');
            $table->string('phone', 15);
            $table->string('ring_group')->nullable();
            $table->boolean('callerid_check')->nullable();
            $table->boolean('owned')->default(0);
            $table->integer('calls');
            $table->integer('connects');
            $table->decimal('connect_ratio');
            $table->boolean('checked')->default(0);
            $table->boolean('flagged')->nullable();
            $table->string('flags')->nullable();
            $table->string('replaced_by', 15)->nullable();
            $table->string('swap_error')->nullable();

            $table->index('call_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('phoneflags')->dropIfExists('daily_phone_flags');
    }
}
