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
            $table->integer('original_patient_id')->unsigned()->index()->nullable();
            $table->string('patient', 25); 

            // This is when the patient is enrolled into ccc program
            $table->string('ccc_no', 25)->nullable();
            $table->string('patient_name', 30)->nullable();
            $table->integer('mother_id')->unsigned()->index();
            $table->tinyInteger('entry_point')->unsigned()->nullable();
            $table->tinyInteger('patient_status')->unsigned()->nullable()->default(1);
            $table->integer('facility_id')->unsigned()->index();

            $table->tinyInteger('hei_validation')->unsigned()->default(0)->nullable();
            $table->string('enrollment_ccc_no', 25)->nullable();
            $table->tinyInteger('enrollment_status')->unsigned()->default(0)->nullable();
            $table->tinyInteger('referredfromsite')->unsigned()->nullable();
            $table->string('otherreason', 50)->nullable(); 
            
            $table->tinyInteger('preferred_language')->nullable();
            $table->string('patient_phone_no', 15)->nullable();
            $table->string('caregiver_phone', 15)->nullable();
            $table->tinyInteger('sex')->unsigned();
            $table->date('dob')->nullable();
            $table->date('dateinitiatedontreatment')->nullable();
            $table->tinyInteger('synched')->default(0);
            $table->date('datesynched')->nullable();
            $table->timestamps();
            

            $table->index(['facility_id', 'patient'], 'eid_patient_unq_index');
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
