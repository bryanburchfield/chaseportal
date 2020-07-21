<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReversalToPlaybookRunTouchActions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('playbook_run_touch_actions', function (Blueprint $table) {
            $table->timestamp('process_started_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('reverse_started_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('playbook_run_touch_actions', function (Blueprint $table) {
            $table->dropColumn('process_started_at');
            $table->dropColumn('processed_at');
            $table->dropColumn('reverse_started_at');
        });
    }
}
