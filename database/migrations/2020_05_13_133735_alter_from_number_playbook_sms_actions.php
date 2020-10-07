<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterFromNumberPlaybookSmsActions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('playbook_sms_actions', function (Blueprint $table) {
            $table->string('from_number', 15)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('playbook_sms_actions', function (Blueprint $table) {
            $table->string('from_number')->change();
        });
    }
}
