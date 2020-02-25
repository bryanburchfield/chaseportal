<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeSubcampaignToJsonOnEmailDripCampaigns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('email_drip_campaigns', function (Blueprint $table) {
            $table->json('subcampaign')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('email_drip_campaigns', function (Blueprint $table) {
            $table->string('subcampaign')->change();
        });
    }
}
