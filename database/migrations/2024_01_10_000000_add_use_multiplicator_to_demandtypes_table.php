<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUseMultiplicatorToDemandtypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('demandtypes', function (Blueprint $table) {
            $table->boolean('useMultiplicator')->default(false)->after('pointsPerShift');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('demandtypes', function (Blueprint $table) {
            $table->dropColumn('useMultiplicator');
        });
    }
}