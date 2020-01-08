<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDncFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dnc_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('group_id');
            $table->bigInteger('user_id');
            $table->string('filename');
            $table->string('description')->nullable();
            $table->timestamp('uploaded_at');
            $table->timestamp('process_started_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('reverse_started_at')->nullable();
            $table->timestamp('reversed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dnc_files');
    }
}
