<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeShiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_shifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedInteger('location');
            $table->float('VM')->default(0);
            $table->float('VM_norm')->default(0);
            $table->float('NM')->default(0);
            $table->float('NM_norm')->default(0);
            $table->float('NIGHT')->default(0);
            $table->float('NIGHT_norm')->default(0);
            $table->float('NEF')->default(0);
            $table->float('NEF_norm')->default(0);
            $table->float('RTW')->default(0);
            $table->float('RTW_norm')->default(0);
            $table->float('KTW')->default(0);
            $table->float('KTW_norm')->default(0);
            $table->float('BKTW')->default(0);
            $table->float('BKTW_norm')->default(0);
            for ($i = 0; $i < 7; $i++) {
                $table->float("weekday_{$i}")->default(0);
                $table->float("weekday_{$i}_norm")->default(0);
            }
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
        Schema::dropIfExists('employee_shifts');
    }
}
