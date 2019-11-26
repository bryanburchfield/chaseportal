<?php

use App\Models\Kpi;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class UpdateKpisLocalization extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kpis', function (Blueprint $table) {
            $table->dropColumn('description');
        });

        foreach (Kpi::all() as $kpi) {
            $kpi->name = Str::snake($kpi->name);
            if ($kpi->name == 'calls&_sales_per_rep') {
                $kpi->name = 'calls_and_sales_per_rep';
            }
            $kpi->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // mostly SOL
        Schema::create('kpis', function (Blueprint $table) {
            $table->string('description', 500);
        });
    }
}
