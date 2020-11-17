<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFlagsToPhoneReswaps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('phoneflags')->table('phone_reswaps', function (Blueprint $table) {
            $table->string('flags')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('phoneflags')->table('phone_reswaps', function (Blueprint $table) {
            $table->dropColumn('flags');
        });
    }
}
