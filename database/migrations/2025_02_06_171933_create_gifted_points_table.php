<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGiftedPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gifted_points', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('receiver_remote_id');
            $table->integer('points');
            $table->date('gifted_at');
            $table->bigInteger('giver_remote_id')->nullable();
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
        Schema::dropIfExists('gifted_points');
    }
}
