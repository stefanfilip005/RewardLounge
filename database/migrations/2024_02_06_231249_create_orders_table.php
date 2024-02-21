<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('remoteId');
            $table->unsignedInteger('total_points')->default(0);
            $table->unsignedTinyInteger('state')->default(0);
            $table->foreign('remoteId')->references('remoteId')->on('employees')->onDelete('cascade');
            $table->timestamps();
            // Add any other fields you need for an order, like status, total price, etc.
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
