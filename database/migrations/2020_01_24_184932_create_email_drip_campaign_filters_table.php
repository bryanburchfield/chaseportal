<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailDripCampaignFiltersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_drip_campaign_filters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('email_drip_campaign_id');
            $table->string('field');
            $table->string('operator', 20);
            $table->string('value')->nullable();
            $table->timestamps();

            $table->foreign('email_drip_campaign_id')
                ->references('id')->on('email_drip_campaigns')
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
        Schema::dropIfExists('email_drip_campaign_filters');
    }
}
