<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailServiceProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_service_providers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('group_id');
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->string('provider_type', 20);
            $table->json('properties')->nullable();
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
        Schema::dropIfExists('email_service_providers');
    }
}
