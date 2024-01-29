<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInfoblaetterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('infoblaetter', function (Blueprint $table) {
            $table->id();
            $table->year('year');
            $table->string('m01')->nullable();
            $table->string('m02')->nullable();
            $table->string('m03')->nullable();
            $table->string('m04')->nullable();
            $table->string('m05')->nullable();
            $table->string('m06')->nullable();
            $table->string('m07')->nullable();
            $table->string('m08')->nullable();
            $table->string('m09')->nullable();
            $table->string('m10')->nullable();
            $table->string('m11')->nullable();
            $table->string('m12')->nullable();
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
        Schema::dropIfExists('infoblaetter');
    }
}
