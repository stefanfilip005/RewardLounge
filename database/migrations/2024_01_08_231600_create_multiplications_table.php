<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMultiplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('multiplications', function (Blueprint $table) {
            $table->id();
            $table->date('from_date');
            $table->date('to_date')->nullable();
            
            // 24 columns for each hour of the day
            for ($hour = 0; $hour < 24; $hour++) {
                $columnName = 'hour_' . str_pad($hour, 2, '0', STR_PAD_LEFT);
                $table->decimal($columnName, 8, 2)->default(1.00);
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
        Schema::dropIfExists('multiplications');
    }
}