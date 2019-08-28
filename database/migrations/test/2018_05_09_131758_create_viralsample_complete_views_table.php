<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateViralsampleCompleteViewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
        CREATE OR REPLACE VIEW viralsample_complete_view AS
        (
          SELECT s.*, b.original_batch_id, b.highpriority, b.datereceived, b.datedispatched, b.site_entry, b.lab_id, b.facility_id, b.batch_complete,
          p.original_patient_id, p.patient_status, p.patient, p.sex, p.dob, p.initiation_date, g.gender_description, rs.name as receivedstatus_name, vp.name as prophylaxis_name, vj.name as justification_name, vs.name as sampletype_name, vpt.name as pmtct_name, vr.name as rejected_name

          FROM viralsamples s
            JOIN viralbatches b ON b.id=s.batch_id
            JOIN viralpatients p ON p.id=s.patient_id
            LEFT JOIN gender g on g.id=p.sex
            LEFT JOIN receivedstatus rs on rs.id=s.receivedstatus
            LEFT JOIN viralregimen vp on vp.id=s.prophylaxis
            LEFT JOIN viraljustifications vj on vj.id=s.justification
            LEFT JOIN viralsampletype vs on vs.id=s.sampletype
            LEFT JOIN viralpmtcttype vpt on vpt.id=s.pmtct
            LEFT JOIN viralrejectedreasons vr on vr.id=s.rejectedreason

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
