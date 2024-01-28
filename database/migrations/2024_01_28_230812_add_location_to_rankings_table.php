<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocationToRankingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rankings', function (Blueprint $table) {
            // Add the location column, unsigned integer and nullable
            $table->unsignedInteger('location')->nullable();
        });
        Schema::table('rankings', function (Blueprint $table) {
            // Drop the old unique constraint
            $table->dropUnique(['year', 'remoteId']);

            // Add the new unique constraint including 'location'
            $table->unique(['year', 'remoteId', 'location']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rankings', function (Blueprint $table) {
            // Drop the location column
            $table->dropColumn('location');
        });/*
        Schema::table('rankings', function (Blueprint $table) {
            // Drop the new unique constraint
            $table->dropUnique(['year', 'remoteId', 'location']);

            // Re-add the old unique constraint
            $table->unique(['year', 'remoteId']);
        });*/
    }
}
