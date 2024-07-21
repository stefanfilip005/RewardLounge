<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddInfosToEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('Status')->default("")->after('employeeType');
            $table->string('BezirksstellenNr')->default("")->after('employeeType');
            $table->string('Mitarbeitertyp')->default("")->after('employeeType');
            $table->string('beurlaubtBis')->default("")->after('employeeType');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('Status');
            $table->dropColumn('BezirksstellenNr');
            $table->dropColumn('Mitarbeitertyp');
            $table->dropColumn('beurlaubtBis');
        });
    }
}
