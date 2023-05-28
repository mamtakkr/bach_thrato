<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::defaultStringLength(191);
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('contact')->nullable();
            $table->string('unique_id',20)->nullable();
            $table->string('email')->unique();
            $table->enum('gender', ['male', 'female']);
            $table->string('password');
            $table->string('forget_password_token',10)->nullable();
            $table->string('image_url')->nullable();
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('device_token')->nullable();
            $table->string('city')->nullable();
            $table->unsignedSmallInteger('state')->default(0);
            $table->unsignedTinyInteger('country')->default(0);
            $table->enum('user_type', ['user','merchant','sub_admin','super_admin']);
            $table->enum('status', ['enable','disable']);
            $table->string('location_lat',250)->nullable();
            $table->string('location_long',250)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
