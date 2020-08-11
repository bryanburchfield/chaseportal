<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AlterDncFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dnc_files', function (Blueprint $table) {
            $table->string('action', 6);
        });

        DB::table('dnc_files')->update(['action' => 'add']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dnc_files', function (Blueprint $table) {
            $table->dropColumn('action');
        });
    }
}
