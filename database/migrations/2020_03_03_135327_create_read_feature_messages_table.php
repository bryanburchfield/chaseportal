<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReadFeatureMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('read_feature_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('feature_message_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('read_at')->useCurrent();

            $table->foreign('user_id')
                ->references('id')->on('users');
            $table->foreign('feature_message_id')
                ->references('id')->on('feature_messages');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('read_feature_messages');
    }
}
