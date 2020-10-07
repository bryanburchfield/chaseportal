<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActionToPlaybookRunDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('playbook_run_details', function (Blueprint $table) {
            $table->unsignedBigInteger('playbook_action_id');

            $table->foreign('playbook_action_id')
                ->references('id')->on('playbook_actions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('playbook_run_details', function (Blueprint $table) {
            $table->dropColumn('playbook_action_id');
        });
    }
}
