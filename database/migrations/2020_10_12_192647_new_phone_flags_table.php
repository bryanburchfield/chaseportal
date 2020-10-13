<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class NewPhoneFlagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('phone_flags');

        Schema::create('phone_flags', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->dateTime('run_date');
            $table->integer('group_id');
            $table->string('group_name');
            $table->string('phone', 15);
            $table->integer('calls');
            $table->decimal('contact_ratio');
            $table->boolean('in_system')->default(0);
            $table->boolean('checked')->default(0);
            $table->string('flagged')->nullable();
            $table->string('replaced_by', 15)->nullable();

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
        Schema::dropIfExists('phone_flags');
    }
}
