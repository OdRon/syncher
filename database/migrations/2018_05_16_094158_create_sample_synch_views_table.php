<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSampleSynchViewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
        CREATE OR REPLACE VIEW sample_synch_view AS
        (
          SELECT s.*, b.original_batch_id, b.highpriority, b.datereceived, b.datedispatched, b.site_entry, b.lab_id, b.lab_id as lab, b.facility_id, b.facility_id as facility, f.partner, f.district as subcounty, d.county, b.batch_complete,
          p.original_patient_id, p.patient_status, p.patient, p.sex, p.dob, p.mother_id 

          FROM samples s
            JOIN batches b ON b.id=s.batch_id
            JOIN patients p ON p.id=s.patient_id

            LEFT JOIN facilitys f ON b.facility_id=f.id
            LEFT JOIN districts d ON d.id=f.district 
        );
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
