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
            $table->string('old_campaign')->nullable();
            $table->string('old_subcampaign')->nullable();
            $table->string('old_callstatus')->nullable();
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
        });
    }
}
