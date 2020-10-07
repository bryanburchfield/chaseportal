<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExtraCampaignsForPlaybooks extends Migration
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
            $table->unsignedBigInteger('contacts_playbook_id');
            $table->string('campaign');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('contacts_playbook_id')
                ->references('id')->on('contacts_playbooks');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('playbook_campaigns');
    }
}
