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
			$table->string('email')->nullable();
			$table->string('phone')->nullable();
			$table->decimal('points')->default(0);
			$table->unsignedBigInteger('shifts')->default(0);
			$table->datetime('lastPointCalculation')->nullable();

			$table->boolean('isAdministrator')->default(false);
			$table->boolean('isModerator')->default(false);
			$table->boolean('isDeveloper')->default(false);
			$table->boolean('showNameInRanking')->default(false);

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
