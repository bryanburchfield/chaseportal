<?php

use App\Models\InternalPhoneCount;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompletedAtToInternalPhoneCounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('phoneflags')->table('internal_phone_counts', function (Blueprint $table) {
            $table->timestamp('completed_at')->nullable();
            $table->index('run_date');
        });

        // update existing recs
        foreach (InternalPhoneCount::all() as $rec) {
            $rec->completed_at = $rec->run_date;
            $rec->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('phoneflags')->table('internal_phone_counts', function (Blueprint $table) {
            $table->dropColumn('completed_at');
            $table->dropIndex('internal_phone_counts_run_date_index');
        });
    }
}
