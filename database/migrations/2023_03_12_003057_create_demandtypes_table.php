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
			$table->string('name')->unique();
			$table->string('description')->default("");
			$table->unsignedDecimal('pointsPerMinute')->default(0);
			$table->unsignedDecimal('pointsPerShift')->default(0);
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
