<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->integer('merchant_id')->nullable();
            $table->integer('cat_id')->nullable();
            $table->string('title', 150)->nullable();
            $table->string('location', 1000)->nullable();
            $table->string('timings')->nullable();
            $table->string('location_lat',250)->nullable();
            $table->string('location_long',250)->nullable();
            $table->string('image_url',250)->nullable();
            $table->enum('favourite',['0','1']);
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
        Schema::dropIfExists('stores');
    }
}
