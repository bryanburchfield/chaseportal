<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSubcampsToNullableLeadRules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lead_rules', function (Blueprint $table) {
            $table->string('source_subcampaign')->nullable()->change();
            $table->string('destination_subcampaign')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lead_rules', function (Blueprint $table) {
            //
        });
    }
}
