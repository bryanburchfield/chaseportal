<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeFlaggedToBoolPhoneFlags extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('phoneflags')->table('phone_flags', function (Blueprint $table) {
            $table->boolean('flagged')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('phoneflags')->table('phone_flags', function (Blueprint $table) {
            $table->string('flagged')->change();
        });
    }
}
