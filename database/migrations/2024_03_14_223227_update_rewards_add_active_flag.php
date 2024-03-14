<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class UpdateRewardsAddActiveFlag extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rewards', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('valid_to');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rewards', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
}
