<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddConnectsToPhoneFlags extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('phone_flags', function (Blueprint $table) {
            $table->integer('connects');
        });

        DB::statement('UPDATE phone_flags SET connects = calls * (connect_ratio / 100)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('phone_flags', function (Blueprint $table) {
            $table->dropColumn('connects');
        });
    }
}
