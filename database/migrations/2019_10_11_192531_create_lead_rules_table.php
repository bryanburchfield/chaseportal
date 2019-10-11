<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lead_rules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id');
            $table->string('source_campaign');
            $table->string('source_subcampaign');
            $table->string('filter_type');
            $table->integer('filter_value');
            $table->string('destination_campaign');
            $table->string('destination_subcampaign');
            $table->text('description')->nullable();
            $table->boolean('active');
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lead_rules');
    }
}
