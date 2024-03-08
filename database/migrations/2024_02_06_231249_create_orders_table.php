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
            $table->text('note')->nullable();
            $table->foreign('remoteId')->references('remoteId')->on('employees')->onDelete('cascade');
            $table->dateTime('created_at_datetime')->nullable();

            $table->dateTime('state_1_datetime')->nullable();
            $table->unsignedBigInteger('state_1_user_id')->nullable();
            $table->foreign('state_1_user_id')->references('remoteId')->on('employees')->onDelete('cascade');
    
            $table->dateTime('state_2_datetime')->nullable();
            $table->unsignedBigInteger('state_2_user_id')->nullable();
            $table->foreign('state_2_user_id')->references('remoteId')->on('employees')->onDelete('cascade');
    
            $table->dateTime('state_3_datetime')->nullable();
            $table->unsignedBigInteger('state_3_user_id')->nullable();
            $table->foreign('state_3_user_id')->references('remoteId')->on('employees')->onDelete('cascade');
    
            $table->dateTime('state_4_datetime')->nullable();
            $table->unsignedBigInteger('state_4_user_id')->nullable();
            $table->foreign('state_4_user_id')->references('remoteId')->on('employees')->onDelete('cascade');

            $table->dateTime('state_5_datetime')->nullable();
            $table->unsignedBigInteger('state_5_user_id')->nullable();
            $table->foreign('state_5_user_id')->references('remoteId')->on('employees')->onDelete('cascade');
    
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
