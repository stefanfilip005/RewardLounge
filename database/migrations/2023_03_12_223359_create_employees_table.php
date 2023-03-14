<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('remoteId')->unique();
			$table->string('firstname')->default("");
			$table->string('lastname')->default("");
			$table->string('email')->default("");
			$table->string('phone')->default("");
			$table->decimal('points')->default(0);
			$table->datetime('lastPointCalculation')->nullable();
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
        Schema::dropIfExists('employees');
    }
}
