<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubcampsToPlaybooks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contacts_playbooks', function (Blueprint $table) {
            $table->dropColumn('subcampaign');
        });

        Schema::create('playbook_subcampaigns', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('contacts_playbook_id');
            $table->string('subcampaign');
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
        Schema::dropIfExists('playbook_subcampaigns');

        Schema::table('contacts_playbooks', function (Blueprint $table) {
            $table->string('subcampaign')->nullable();
        });
    }
}
