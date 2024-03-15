<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateFutureOpenShiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('futureOpenShifts', function (Blueprint $table) {
            $table->id();
			$table->datetime('start');
			$table->datetime('end');
			$table->string('demandType');
			$table->string('shiftType');
			$table->unsignedBigInteger('location');
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
        Schema::dropIfExists('futureOpenShifts');
    }
}
