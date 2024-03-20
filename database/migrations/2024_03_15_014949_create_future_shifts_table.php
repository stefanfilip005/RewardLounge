<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateFutureShiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('futureShifts', function (Blueprint $table) {
            $table->id();
            /*
			$table->datetime('start');
			$table->datetime('end');
			$table->string('demandType');
			$table->unsignedBigInteger('location');
            */


            $table->integer('Teil')->nullable();
            $table->string('Verwendung')->nullable();
            $table->string('Schicht')->nullable();
            $table->bigInteger('Id')->nullable();
            $table->string('KlassId')->nullable();
            $table->boolean('IstVollst')->nullable();
            $table->dateTime('Datum')->nullable();
            $table->dateTime('Beginn')->nullable();
            $table->dateTime('Ende')->nullable();
            $table->dateTime('PoolBeginn')->nullable();
            $table->dateTime('PoolEnde')->nullable();
            $table->string('Bezeichnung')->nullable();
            $table->string('ObjektId')->nullable();
            $table->string('ObjektBezeichnung1')->nullable();
            $table->string('ObjektBezeichnung2')->nullable();
            $table->string('ObjektInfo')->nullable();
            $table->text('PlanInfo')->nullable();
            $table->boolean('IstForderer')->nullable();
            $table->bigInteger('VaterId')->nullable();
            $table->boolean('IstOptional')->nullable();
            $table->bigInteger('PoolId')->nullable();
            $table->integer('PoolTeil')->nullable();
            $table->integer('DienstartId')->nullable();
            $table->string('DienstartBeschreibung')->nullable();
            $table->string('ChgUserAnzeigename')->nullable();
            $table->string('ChgUserLoginname')->nullable();
            $table->dateTime('ChgDate')->nullable();
            $table->integer('AbteilungId')->nullable();
            $table->string('AbteilungBezeichnung')->nullable();
            $table->string('AbteilungKZ')->nullable();
            $table->text('Info')->nullable();
            $table->dateTime('TimeStamp')->nullable();
            $table->boolean('Processed')->nullable();
            $table->string('MessageSent')->nullable();
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
        Schema::dropIfExists('futureShifts');
    }
}
