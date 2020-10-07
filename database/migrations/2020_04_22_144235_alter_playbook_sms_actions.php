<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPlaybookSmsActions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('playbook_sms_actions', function (Blueprint $table) {
            $table->dropColumn('message');
            $table->integer('template_id');
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
            $table->dropColumn('template_id');
            $table->text('message');
        });
    }
}
