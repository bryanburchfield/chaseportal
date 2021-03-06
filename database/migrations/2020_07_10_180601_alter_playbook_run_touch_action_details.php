<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPlaybookRunTouchActionDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('playbook_run_touch_action_details', function (Blueprint $table) {
            $table->string('old_campaign', 50)->nullable();
            $table->string('old_subcampaign', 50)->nullable();
            $table->string('old_callstatus', 50)->nullable();
            $table->string('old_email')->nullable();
            $table->string('old_phone', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('playbook_run_touch_action_details', function (Blueprint $table) {
            $table->dropColumn('old_campaign');
            $table->dropColumn('old_subcampaign');
            $table->dropColumn('old_callstatus');
            $table->dropColumn('old_email');
            $table->dropColumn('old_phone');
        });
    }
}
