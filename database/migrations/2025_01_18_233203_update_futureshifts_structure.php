<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateFutureshiftsStructure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('futureShifts', 'futureShifts_old');

        Schema::create('futureShifts', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('shift_id'); // Identifier from the new API
            $table->date('date'); // Shift date
            $table->dateTime('begin'); // Shift start time
            $table->dateTime('end'); // Shift end time
            $table->string('vehicle_type'); // Typ from parent shift
            $table->string('vehicle_type_id'); // Typ ID from parent shift
            $table->string('role'); // Role of the employee (typ in resources)
            $table->string('role_id'); // Role ID of the employee (typid in resources)
            $table->string('employee_id'); // Employee's MNR
            $table->string('employee_name')->nullable(); // Employee's name
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
        Schema::dropIfExists('futureShifts');
        Schema::rename('futureShifts_old', 'futureShifts');
    }
}
