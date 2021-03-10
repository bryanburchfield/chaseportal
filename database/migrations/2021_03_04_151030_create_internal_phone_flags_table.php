<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInternalPhoneFlagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('phoneflags')->create('internal_phone_flags', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->dateTime('run_date');
            $table->integer('group_id');
            $table->string('group_name');
            $table->smallInteger('dialer_numb');
            $table->string('phone', 15);
            $table->string('ring_group')->nullable();
            $table->string('subcampaigns')->nullable();
            $table->integer('dials')->nullable();
            $table->integer('connects')->nullable();
            $table->decimal('connect_pct')->nullable();
            $table->string('replaced_by', 15)->nullable();
            $table->string('swap_error')->nullable();

            $table->index('run_date', 'phone');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('phoneflags')->dropIfExists('internal_phone_flags');
    }
}
