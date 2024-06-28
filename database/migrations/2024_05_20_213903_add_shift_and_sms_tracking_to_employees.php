<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShiftAndSmsTrackingToEmployees extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dateTime('last_shift_date')->nullable()->after('showNameInRanking');
            $table->dateTime('next_shift_date')->nullable()->after('last_shift_date');
            $table->dateTime('last_sms_sent')->nullable()->after('next_shift_date');
            $table->integer('sms_count')->default(0)->after('last_sms_sent');
            $table->string('employeeType')->nullable()->after('sms_count');
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
            $table->dropColumn(['last_shift_date', 'next_shift_date', 'last_sms_sent', 'sms_count','employeeType']);
        });
    }
}
