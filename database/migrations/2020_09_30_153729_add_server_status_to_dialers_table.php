<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddServerStatusToDialersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dialers', function (Blueprint $table) {
            $table->string('status_url')->nullable();
        });

        DB::statement("UPDATE dialers SET status_url = 'https://ad25c822-bd4d-4177-8da3-e2c605524735.statuscast.com/'  WHERE dialer_numb = 7");
        DB::statement("UPDATE dialers SET status_url = 'https://c2493d6d-dcc7-4c1d-86f9-6f3f68cdf63c.statuscast.com/'  WHERE dialer_numb = 24");
        DB::statement("UPDATE dialers SET status_url = 'https://d22aa892-5822-45e2-bcb9-d01fa73e26fa.statuscast.com/'  WHERE dialer_numb = 26");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dialers', function (Blueprint $table) {
            $table->dropColumn('status_url');
        });
    }
}
