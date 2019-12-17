<?php

use App\Models\LeadRule;
use App\Models\LeadRuleFilter;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadRuleFiltersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lead_rule_filters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('lead_rule_id');
            $table->string('filter_type');
            $table->integer('filter_value');
        });

        foreach (LeadRule::withTrashed()->get() as $lead_rule) {
            LeadRuleFilter::create([
                'lead_rule_id' => $lead_rule->id,
                'filter_type' => $lead_rule->filter_type,
                'filter_value' => $lead_rule->filter_value,
            ]);
        }

        Schema::table('lead_rules', function (Blueprint $table) {
            $table->dropColumn('filter_type');
            $table->dropColumn('filter_value');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lead_rule_filters');
    }
}
