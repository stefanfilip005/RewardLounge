<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBallSlotsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('ball_slots', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('mtr_number')
                  ->nullable()
                  ->comment('Manuelle Nummerierung / Zuordnung zum A4-Layout');

            $table->string('label'); // z.B. "Gäste zum Tisch führen 19:00–20:00 (Platz 1)"

            $table->string('helper_name')->nullable();
            $table->string('helper_phone')->nullable();
            $table->string('helper_email')->nullable();

            $table->unsignedBigInteger('filled_by_user_id')->nullable();

            $table->timestamp('filled_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('ball_slots');
    }
}
