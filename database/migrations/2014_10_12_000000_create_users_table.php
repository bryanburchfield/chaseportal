<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('user_type', 10)->default('client');
            $table->integer('group_id');
            $table->string('db', 100);
            $table->string('tz', 100);
            $table->string('app_token', 100)->nullable();
            $table->string('additional_dbs')->nullable();
            $table->text('persist_filters')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        DB::table('users')->insert(
            array(
                'name' => 'Test',
                'email' => 'test@chase.com',
                'password' => Hash::make('test'),
                'user_type' => 'admin',
                'group_id' => 777,
                'db' => 'PowerV2_Reporting_Dialer-17',
                'tz' => 'Eastern Standard Time',
                'app_token' => 'F1C1592588411002AF340CBAEDD6FC33',
            )
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
