<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateRankingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rankings', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('year');
			$table->unsignedBigInteger('remoteId');
			$table->unsignedBigInteger('place');
			$table->unsignedBigInteger('points');
			$table->unsignedBigInteger('pointsForNext');
            $table->timestamps();

            $table->unique(['year', 'remoteId']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rankings');
    }
}
