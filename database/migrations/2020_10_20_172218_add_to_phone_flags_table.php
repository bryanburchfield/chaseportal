<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddToPhoneFlagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('phone_flags', function (Blueprint $table) {
            $table->renameColumn('contact_ratio', 'connect_ratio');
            $table->boolean('callerid_check')->nullable();
            $table->string('swap_error')->nullable();
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
            $table->renameColumn('connect_ratio', 'contact_ratio');
            $table->dropColumn('callerid_check');
            $table->dropColumn('swap_error');
        });
    }
}
