<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateRewardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();

			$table->string('name');
			$table->string('slogan');
			$table->text('description')->nullable();
			$table->string('src1')->nullable();
			$table->string('src2')->nullable();
			$table->string('src3')->nullable();
			$table->unsignedInteger('points');
			$table->unsignedInteger('euro')->nullable();

			$table->unsignedInteger('quantity')->nullable();

			$table->date('valid_from');
			$table->date('valid_to')->nullable();

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
        Schema::dropIfExists('rewards');
    }
}
