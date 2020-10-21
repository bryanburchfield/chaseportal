<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenamedActivePhoneFlagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('phone_flags', function (Blueprint $table) {
            $table->renameColumn('in_system', 'owned');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('phone_flags', function (Blueprint $table) {
            $table->renameColumn('owned', 'in_system');
        });
    }
}
