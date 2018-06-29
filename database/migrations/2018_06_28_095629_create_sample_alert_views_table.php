<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSampleAlertViewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
        CREATE OR REPLACE VIEW sample_alert_view AS
        (
          SELECT s.*, b.original_batch_id, b.highpriority, b.datereceived, b.datedispatched, b.site_entry, b.batch_complete,
          p.original_patient_id, p.patient_status, p.patient, p.sex, p.dob, p.mother_id,
          p.hei_validation, p.enrollment_ccc_no, p.enrollment_status, p.referredfromsite, p.otherreason,
           b.lab_id, b.facility_id, f.name as facility, 
           f.partner as partner_id, pa.name as partner, 
           f.district as subcounty_id, d.name as subcounty,
           d.county as county_id, c.name as county

          FROM samples s
            JOIN batches b ON b.id=s.batch_id
            JOIN patients p ON p.id=s.patient_id

            LEFT JOIN facilitys f ON b.facility_id=f.id
            LEFT JOIN districts d ON d.id=f.district 
            LEFT JOIN countys c ON c.id=d.county 
            LEFT JOIN partners pa ON pa.id=f.partner 
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
