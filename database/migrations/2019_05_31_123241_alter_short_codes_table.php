<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterShortCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::dropIfExists('short_code_queries');
        // Schema::create('short_code_queries', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->tinyInteger('testtype')->comment('1: EID; 2:VL')->nullable();
        //     $table->string('phoneno', 30)->nullable();
        //     $table->string('message', 50)->nullable();
        //     $table->integer('facility_id')->nullable();
        //     $table->integer('patient_id')->nullable();
        //     $table->tinyInteger('status')->default(0);
        //     $table->dateTime('datereceived')->nullable();
        //     $table->dateTime('dateresponded')->nullable();
        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
