<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailDripSendsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_drip_sends', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('email_drip_campaign_id');
            $table->integer('lead_id');
            $table->timestamp('emailed_at');

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
        Schema::dropIfExists('email_drip_sends');
    }
}
