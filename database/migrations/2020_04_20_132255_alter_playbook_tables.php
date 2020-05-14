<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPlaybookTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('playbook_campaign_filters', function (Blueprint $table) {
            $table->renameColumn('playbook_campaign_id', 'contacts_playbook_id');
        });

        Schema::table('playbook_campaign_actions', function (Blueprint $table) {
            $table->renameColumn('playbook_campaign_id', 'contacts_playbook_id');
        });

        Schema::table('playbook_histories', function (Blueprint $table) {
            $table->renameColumn('playbook_campaign_id', 'contacts_playbook_id');
        });

        Schema::rename('playbook_campaigns', 'contacts_playbooks');
        Schema::rename('playbook_campaign_filters', 'contacts_playbook_filters');
        Schema::rename('playbook_campaign_actions', 'contacts_playbook_actions');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('contacts_playbooks', 'playbook_campaigns');
        Schema::rename('contacts_playbook_filters', 'playbook_campaign_filters');
        Schema::rename('contacts_playbook_actions', 'playbook_campaign_actions');

        Schema::table('playbook_campaign_filters', function (Blueprint $table) {
            $table->renameColumn('contacts_playbook_id', 'playbook_campaign_id');
        });

        Schema::table('playbook_campaign_actions', function (Blueprint $table) {
            $table->renameColumn('contacts_playbook_id', 'playbook_campaign_id');
        });

        Schema::table('playbook_histories', function (Blueprint $table) {
            $table->renameColumn('contacts_playbook_id', 'playbook_campaign_id');
        });
    }
}
