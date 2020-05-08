<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailDripCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_drip_campaigns', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('group_id');
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('campaign');
            $table->string('subcampaign')->nullable();
            $table->string('subject');
            $table->string('from');
            $table->string('email_field');
            $table->unsignedBigInteger('email_service_provider_id');
            $table->integer('template_id');
            $table->boolean('active')->default(false);
            $table->smallInteger('emails_per_lead');
            $table->smallInteger('days_between_emails')->nullable();
            $table->timestamp('last_run_from')->nullable();
            $table->timestamp('last_run_to')->nullable();
            $table->timestamps();

            $table->foreign('email_service_provider_id')
                ->references('id')->on('email_service_providers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_drip_campaigns');
    }
}
