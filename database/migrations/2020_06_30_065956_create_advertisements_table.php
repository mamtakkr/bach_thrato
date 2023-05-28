<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvertisementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->string('title', 250)->nullable();
            $table->string('image_url', 250)->nullable();
            $table->string('link', 250)->nullable();
            $table->string('button_link', 100)->nullable();
            $table->enum('location', ['top','bottom'])->nullable();
            $table->float('height')->nullable();
            $table->float('width')->nullable();
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
        Schema::dropIfExists('advertisements');
    }
}
