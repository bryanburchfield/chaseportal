<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PlaybookRunsAndSmsChanges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('playbook_histories');

        Schema::create('playbook_runs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('contacts_playbook_id');
            $table->timestamps();

            $table->foreign('contacts_playbook_id')
                ->references('id')->on('contacts_playbooks')
                ->onDelete('cascade');
        });

        Schema::create('playbook_run_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('playbook_run_id');
            $table->string('reporting_db', 50);
            $table->bigInteger('lead_id');
            $table->timestamps();

            $table->foreign('playbook_run_id')
                ->references('id')->on('playbook_runs')
                ->onDelete('cascade');
        });

        Schema::table('playbook_sms_actions', function (Blueprint $table) {
            $table->smallInteger('sms_per_lead');
            $table->smallInteger('days_between_sms')->nullable();
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
            $table->dropColumn('sms_per_lead');
            $table->dropColumn('days_between_sms');
        });

        Schema::create('playbook_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('contacts_playbook_id');
            $table->string('reporting_db', 50);
            $table->bigInteger('lead_id');
            $table->timestamps();

            $table->foreign('contacts_playbook_id')
                ->references('id')->on('contacts_playbooks')
                ->onDelete('cascade');
        });

        Schema::dropIfExists('playbook_run_details');
        Schema::dropIfExists('playbook_runs');
    }
}
