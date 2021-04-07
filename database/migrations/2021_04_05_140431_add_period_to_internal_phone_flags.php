<?php

use App\Models\InternalPhoneFlag;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class AddPeriodToInternalPhoneFlags extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('phoneflags')->table('internal_phone_flags', function (Blueprint $table) {
            $table->string('period', 20)->after('run_date')->nullable();
        });

        // update existing recs
        foreach (InternalPhoneFlag::all()->pluck('run_date')->unique() as $timestamp) {
            $time = (int)Carbon::parse($timestamp)->tz('America/New_York')->format('H');
            $period = 'evening';
            if ($time >= 7 && $time <= 11) {
                $period = 'morning';
            }
            if ($time >= 12 && $time <= 16) {
                $period = 'afternoon';
            }

            InternalPhoneFlag::where('run_date', $timestamp)->update(['period' => $period]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('phoneflags')->table('internal_phone_flags', function (Blueprint $table) {
            $table->dropColumn('period');
        });
    }
}
