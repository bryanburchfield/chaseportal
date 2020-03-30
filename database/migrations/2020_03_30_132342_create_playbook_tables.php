<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlaybookTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('playbook_campaigns', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('group_id');
            $table->string('name');
            $table->string('campaign');
            $table->string('subcampaign')->nullable();
            $table->timestamp('last_run_from')->nullable();
            $table->timestamp('last_run_to')->nullable();
            $table->boolean('active');
            $table->timestamps();
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
        });

        Schema::create('playbook_sms_actions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('playbook_action_id');
            $table->string('from_number');
            $table->text('message');
            $table->timestamps();

            $table->foreign('playbook_action_id')
                ->references('id')->on('playbook_actions')
                ->onDelete('cascade');
        });

        Schema::create('playbook_lead_actions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('playbook_action_id');
            $table->string('to_campaign')->nullable();
            $table->string('to_subcampaign')->nullable();
            $table->string('to_callstatus')->nullable();
            $table->timestamps();

            $table->foreign('playbook_action_id')
                ->references('id')->on('playbook_actions')
                ->onDelete('cascade');
        });

        Schema::create('playbook_email_actions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('playbook_action_id');
            $table->unsignedBigInteger('email_service_provider_id');
            $table->string('subject');
            $table->string('from');
            $table->string('email_field');
            $table->integer('template_id');
            $table->smallInteger('emails_per_lead');
            $table->smallInteger('days_between_emails')->nullable();
            $table->timestamps();

            $table->foreign('playbook_action_id')
                ->references('id')->on('playbook_actions')
                ->onDelete('cascade');
            $table->foreign('email_service_provider_id')
                ->references('id')->on('email_service_providers');
        });

        Schema::create('playbook_campaign_filters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('playbook_campaign_id');
            $table->unsignedBigInteger('playbook_filter_id');
            $table->timestamps();

            $table->foreign('playbook_campaign_id')
                ->references('id')->on('playbook_campaigns')
                ->onDelete('cascade');
            $table->foreign('playbook_filter_id')
                ->references('id')->on('playbook_filters');
        });

        Schema::create('playbook_campaign_actions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('playbook_campaign_id');
            $table->unsignedBigInteger('playbook_action_id');
            $table->timestamps();

            $table->foreign('playbook_campaign_id')
                ->references('id')->on('playbook_campaigns')
                ->onDelete('cascade');
            $table->foreign('playbook_action_id')
                ->references('id')->on('playbook_actions');
        });

        Schema::create('playbook_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('playbook_campaign_id');
            $table->string('reporting_db', 50);
            $table->bigInteger('lead_id');
            $table->timestamps();

            $table->foreign('playbook_campaign_id')
                ->references('id')->on('playbook_campaigns')
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
        Schema::dropIfExists('playbook_tables');
    }
}
