<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDncFileDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dnc_file_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('dnc_file_id');
            $table->string('phone', 20);
            $table->timestamp('processed_at')->nullable();
            $table->boolean('succeeded')->nullable();
            $table->string('error')->nullable();

            $table->foreign('dnc_file_id')
                ->references('id')->on('dnc_files')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dnc_file_details');
    }
}
