<?php

namespace App;

use Illuminate\Support\Facades\Cache;
use DB;

use Carbon\Carbon;

class Lookup
{


    public static function calculate_age($date_collected, $dob)
    {
    	if(!$dob) return 0;
        $dob = Carbon::parse( $dob );
        $dc = Carbon::parse( $date_collected );
        $months = $dc->diffInMonths($dob);
        $weeks = $dc->diffInWeeks($dob->copy()->addMonths($months));
        $total = $months + ($weeks / 4);
        if($total == 0) $total = 0.1;
        return $total;
    }

    public static function calculate_viralage($date_collected, $dob)
    {
    	if(!$dob) return 0;
        $dob = Carbon::parse( $dob );
        $dc = Carbon::parse( $date_collected );
        $years = $dc->diffInYears($dob, true);

        if($years == 0) $years = ($dc->diffInMonths($dob)/12);
        return $years;
    }

    public static function calculate_dob($date_collected, $years, $months)
    {
    	if(!$years && !$months) return null;
    	$dc = Carbon::createFromFormat('Y-m-d', $date_collected);
    	$dc->subYears($years);
    	$dc->sumMonths($months);
    	return $dc->toDateString();
    }

    public static function resolve_gender($value)
    {
        $value = trim($value);
        $value = strtolower($value);
        if(str_contains($value, ['m', '1'])){
            return 1;
        }
        else if(str_contains($value, ['f', '2'])){
            return 2;
        }
        // else if($value == 'No Data' || $value == 'no data'){
        //     return 3;
        // }
        // else if (is_numeric($value)){
        //     $value = (int) $value;
        //     if($value < 3) return $value;
        //     return $value;
        // }
        else{
            return 3;
        }
    }


    public static function samples_arrays()
    {
        return [

            'batch' => ['original_batch_id' , 'highpriority', 'inputcomplete', 'batch_complete', 'site_entry', 'sent_email', 'printedby', 'user_id', 'lab_id', 'facility_id', 'datedispatchedfromfacility', 'datereceived', 'datebatchprinted', 'datedispatched', 'dateindividualresultprinted'],

            'mother' => ['hiv_status', 'facility_id', 'ccc_no'],

            'patient' => ['original_patient_id', 'patient', 'patient_name', 'sex', 'facility_id', 'caregiver_phone', 'dob', 'entry_point', 'dateinitiatedontreatment'],

            'sample' => ['id', 'original_sample_id', 'amrs_location', 'provider_identifier', 'order_no', 'sample_type', 'receivedstatus', 'age', 'pcrtype', 'regimen', 'mother_prophylaxis', 'feeding', 'spots', 'comments', 'labcomment', 'parentid', 'rejectedreason', 'reason_for_repeat', 'interpretation', 'result', 'worksheet_id', 'hei_validation', 'enrollment_ccc_no', 'enrollmentstatus', 'referredfromsite', 'otherreason', 'flag', 'run', 'repeatt', 'eqa', 'approvedby', 'approvedby2', 'datecollected', 'datetested', 'datemodified', 'dateapproved', 'dateapproved2', 'tat1', 'tat2', 'tat3', 'tat4', 'previous_positive', 'synched', 'datesynched' ], 
        ];
    }

    public static function viralsamples_arrays()
    {
        return [

            'batch' => ['original_batch_id' , 'highpriority', 'inputcomplete', 'batch_complete', 'site_entry', 'sent_email', 'printedby', 'user_id', 'lab_id', 'facility_id', 'datedispatchedfromfacility', 'datereceived', 'datebatchprinted', 'datedispatched', 'dateindividualresultprinted'],

            'patient' => ['original_patient_id', 'patient', 'sex', 'patient_name', 'facility_id', 'caregiver_phone', 'patient', 'dob', 'initiation_date'],

            'sample' => ['id', 'original_sample_id', 'amrs_location', 'provider_identifier', 'order_no', 'vl_test_request_no', 'receivedstatus', 'age', 'age_category', 'justification', 'other_justification', 'sampletype', 'prophylaxis', 'regimenline', 'pmtct', 'dilutionfactor', 'dilutiontype', 'comments', 'labcomment', 'parentid', 'rejectedreason', 'reason_for_repeat', 'interpretation', 'result', 'rcategory', 'units', 'worksheet_id', 'flag', 'run', 'repeatt', 'approvedby', 'approvedby2', 'datecollected', 'datetested', 'datemodified', 'dateapproved', 'dateapproved2', 'tat1', 'tat2', 'tat3', 'tat4', 'previous_nonsuppressed', 'synched', 'datesynched' ],
            
        ];
    }
}
