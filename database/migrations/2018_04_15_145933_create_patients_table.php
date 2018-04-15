<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('original_patient_id')->unsigned()->index();
            $table->string('patient', 50);
            $table->string('patient_name', 50)->nullable();
            $table->integer('mother_id')->unsigned();
            $table->integer('entry_point')->unsigned()->index();
            $table->integer('facility_id')->unsigned()->index();
            $table->string('caregiver_phone', 15)->nullable();
            $table->tinyInteger('sex')->unsigned()->index();
            $table->date('dob')->index();
            $table->date('dateinitiatedontreatment')->nullable();
            $table->tinyInteger('synched')->default(0);
            $table->date('datesynched')->nullable();
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
        Schema::dropIfExists('patients');
    }
}
