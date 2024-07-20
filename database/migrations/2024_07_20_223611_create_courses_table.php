<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->string('von', 5);  // Time format HH:MM
            $table->string('bis', 5);  // Time format HH:MM
            $table->date('date');
            $table->string('info')->nullable();
            $table->string('name');
            $table->boolean('breitenausbildung')->nullable()->default(null);
            $table->timestamps();

            $table->unique('course_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('courses');
    }
}
