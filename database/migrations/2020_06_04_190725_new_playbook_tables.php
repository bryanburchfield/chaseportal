<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class NewPlaybookTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //  Drop all old tables
        Schema::dropIfExists('playbook_run_details');
        Schema::dropIfExists('playbook_runs');
        Schema::dropIfExists('contacts_playbook_actions');
        Schema::dropIfExists('contacts_playbook_filters');
        Schema::dropIfExists('playbook_email_actions');
        Schema::dropIfExists('playbook_lead_actions');
        Schema::dropIfExists('playbook_sms_actions');
        Schema::dropIfExists('playbook_actions');
        Schema::dropIfExists('playbook_filters');
        Schema::dropIfExists('playbook_optouts');
        Schema::dropIfExists('contacts_playbooks');
        Schema::dropIfExists('playbook_sms_numbers');

        // Now create new tables
        Schema::create('sms_from_numbers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('group_id');
            $table->string('from_number', 15);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['group_id', 'from_number', 'deleted_at']);
        });

        Schema::create('playbook_optouts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('group_id');
            $table->string('email');
            $table->timestamps();

            $table->unique(['group_id', 'email']);
        });

        Schema::create('playbook_filters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('group_id');
            $table->string('name');
            $table->string('campaign')->nullable();
            $table->string('field');
            $table->string('operator', 20);
            $table->string('value')->nullable();
            $table->timestamps();
        });

        Schema::create('playbook_actions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('group_id');
            $table->string('name');
            $table->string('campaign')->nullable();
            $table->string('action_type', 10);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('playbook_sms_actions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('playbook_action_id');
            $table->unsignedBigInteger('sms_from_number_id');
            $table->integer('template_id');
            $table->smallInteger('sms_per_lead');
            $table->smallInteger('days_between_sms')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('playbook_action_id')
                ->references('id')->on('playbook_actions');
            $table->foreign('sms_from_number_id')
                ->references('id')->on('sms_from_numbers');
        });

        Schema::create('playbook_lead_actions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('playbook_action_id');
            $table->string('to_campaign')->nullable();
            $table->string('to_subcampaign')->nullable();
            $table->string('to_callstatus')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('playbook_action_id')
                ->references('id')->on('playbook_actions');
        });

        Schema::create('playbook_email_actions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('playbook_action_id');
            $table->unsignedBigInteger('email_service_provider_id');
            $table->string('email_field');
            $table->string('from');
            $table->string('subject');
            $table->integer('template_id');
            $table->smallInteger('emails_per_lead');
            $table->smallInteger('days_between_emails')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('playbook_action_id')
                ->references('id')->on('playbook_actions');
            $table->foreign('email_service_provider_id')
                ->references('id')->on('email_service_providers');
        });

        Schema::create('contacts_playbooks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('group_id');
            $table->string('name');
            $table->string('campaign');
            $table->string('subcampaign')->nullable();
            $table->timestamp('last_run_from')->nullable();
            $table->timestamp('last_run_to')->nullable();
            $table->boolean('active')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('playbook_touches', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('contacts_playbook_id');
            $table->string('name');
            $table->boolean('active')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('contacts_playbook_id')
                ->references('id')->on('contacts_playbooks');
        });

        Schema::create('playbook_touch_filters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('playbook_touch_id');
            $table->unsignedBigInteger('playbook_filter_id');
            $table->timestamps();

            $table->foreign('playbook_touch_id')
                ->references('id')->on('playbook_touches');
            $table->foreign('playbook_filter_id')
                ->references('id')->on('playbook_filters');
        });

        Schema::create('playbook_touch_actions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('playbook_touch_id');
            $table->unsignedBigInteger('playbook_action_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('playbook_touch_id')
                ->references('id')->on('playbook_touches');
            $table->foreign('playbook_action_id')
                ->references('id')->on('playbook_actions');
        });

        Schema::create('playbook_runs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('contacts_playbook_id');
            $table->timestamps();

            $table->foreign('contacts_playbook_id')
                ->references('id')->on('contacts_playbooks');
        });

        Schema::create('playbook_run_touches', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('playbook_run_id');
            $table->unsignedBigInteger('playbook_touch_id');
            $table->timestamps();

            $table->foreign('playbook_run_id')
                ->references('id')->on('playbook_runs')
                ->onDelete('cascade');
            $table->foreign('playbook_touch_id')
                ->references('id')->on('playbook_touches');
        });

        Schema::create('playbook_run_touch_actions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('playbook_run_touch_id');
            $table->unsignedBigInteger('playbook_action_id');
            $table->timestamp('reversed_at')->nullable();
            $table->timestamps();

            $table->foreign('playbook_run_touch_id')
                ->references('id')->on('playbook_run_touches')
                ->onDelete('cascade');
            $table->foreign('playbook_action_id')
                ->references('id')->on('playbook_actions');
        });

        Schema::create('playbook_run_touch_action_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('playbook_run_touch_action_id');
            $table->string('reporting_db', 50);
            $table->bigInteger('lead_id');
            $table->timestamps();

            $table->foreign('playbook_run_touch_action_id', 'rtad_rta_foreign')
                ->references('id')->on('playbook_run_touch_actions')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // remove new tables - not gonna try to recreate all the old
        Schema::dropIfExists('playbook_run_touch_action_details');
        Schema::dropIfExists('playbook_run_touch_actions');
        Schema::dropIfExists('playbook_run_touches');
        Schema::dropIfExists('playbook_runs');
        Schema::dropIfExists('playbook_touch_actions');
        Schema::dropIfExists('playbook_touch_filters');
        Schema::dropIfExists('playbook_email_actions');
        Schema::dropIfExists('playbook_lead_actions');
        Schema::dropIfExists('playbook_sms_actions');
        Schema::dropIfExists('playbook_actions');
        Schema::dropIfExists('playbook_filters');
        Schema::dropIfExists('playbook_optouts');
        Schema::dropIfExists('playbook_touches');
        Schema::dropIfExists('sms_from_numbers');
        Schema::dropIfExists('contacts_playbooks');
    }
}
