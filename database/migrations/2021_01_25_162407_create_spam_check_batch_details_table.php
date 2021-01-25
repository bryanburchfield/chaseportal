<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpamCheckBatchDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spam_check_batch_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('spam_check_batch_id');
            $table->string('phone', 15);
            $table->boolean('checked')->default(0);
            $table->boolean('flagged')->nullable();
            $table->string('flags')->nullable();
            $table->timestamps();

            $table->foreign('spam_check_batch_id')
                ->references('id')->on('spam_check_batches');

            $table->index('phone');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('spam_check_batch_details');
    }
}
