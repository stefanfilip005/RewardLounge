<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateDemandtypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('demandtypes', function (Blueprint $table) {
            $table->id();
			$table->string('name');
			$table->string('description');
			$table->unsignedDecimal('pointsPerMinute');
			$table->unsignedDecimal('pointsPerShift');
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
        Schema::dropIfExists('demandtypes');
    }
}
