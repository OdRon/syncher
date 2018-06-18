<?php

namespace App;

use Carbon\Carbon;

class Lookup
{

    public static function calculate_age($datecollected, $dob)
    {
    	if(!$dob) return 0;
        $dob = Carbon::parse( $dob );
        $dc = Carbon::parse( $datecollected );
        $months = $dc->diffInMonths($dob);
        $weeks = $dc->diffInWeeks($dob->copy()->addMonths($months));
        $total = $months + ($weeks / 4);
        if($total == 0) $total = 0.1;
        return $total;
    }

    public static function calculate_viralage($datecollected, $dob)
    {
    	if(!$dob) return 0;
        $dob = Carbon::parse( $dob );
        $dc = Carbon::parse( $datecollected );
        $years = $dc->diffInYears($dob, true);

        if($years == 0) $years = ($dc->diffInMonths($dob)/12);
        return $years;
    }

    public static function calculate_dob($datecollected, $years, $months, $class_name=null, $patient=null, $facility_id=null)
    {        
        $datecollected = self::clean_date($datecollected);

        if((!$years && !$months) || !$datecollected || $datecollected == '0000-00-00'){
            if(!$class_name) return null;
            $row = $class_name::where(['patient' => $patient, 'facility_id' => $facility_id])
                        ->where('age', '!=', 0)
                        ->whereNotIn('datecollected', ['0000-00-00', ''])
                        ->whereNotNull('datecollected')
                        ->get()->first();
            if($row){
                $mydate = self::clean_date($row->datecollected);
                if(!$mydate) return null;

                if($class_name == "App\OldViralsampleView"){ 
                    return self::calculate_dob($row->datecollected, $row->age, 0);
                }
                return self::calculate_dob($row->datecollected, 0, $row->age);
            }   
            return null;         
        }

        try {           
            $dc = Carbon::createFromFormat('Y-m-d', $datecollected);
            $dc->subYears($years);
            $dc->subMonths($months);
            return $dc->toDateString();
            
        } catch (Exception $e) {
            return null;
        }
        return null;
    }

    public static function resolve_gender($value, $class_name=null, $patient=null, $facility_id=null)
    {
        $value = trim($value);
        $value = strtolower($value);
        if(str_contains($value, ['m', '1'])){
            return 1;
        }
        else if(str_contains($value, ['f', '2'])){
            return 2;
        }
        else{
            if(!$class_name) return 3;
            $row = $class_name::where(['patient' => $patient, 'facility_id' => $facility_id])
                        ->whereRaw("(gender = 'M' or gender = 'F')")->get()->first();
            if($row) return self::resolve_gender($row->gender);
            return 3;
        }
    }

    public static function clean_date($mydate)
    {
        if(!$mydate || $mydate == '0000-00-00') return null;

        try {
            $my = Carbon::parse($mydate);
            return $my->toDateString();
        } catch (Exception $e) {
            return null;
        }
    }


    public static function samples_arrays()
    {
        return [

            'batch' => ['original_batch_id' , 'highpriority', 'input_complete', 'batch_complete', 'site_entry', 'sent_email', 'printedby', 'user_id', 'lab_id', 'facility_id', 'datedispatchedfromfacility', 'datereceived', 'datebatchprinted', 'datedispatched', 'dateindividualresultprinted'],

            'mother' => ['hiv_status', 'facility_id', 'ccc_no'],

            'patient' => ['original_patient_id', 'patient', 'patient_name', 'sex', 'facility_id', 'caregiver_phone', 'dob', 'entry_point', 'dateinitiatedontreatment'],

            'sample' => ['id', 'original_sample_id', 'amrs_location', 'provider_identifier', 'order_no', 'sample_type', 'receivedstatus', 'age', 'redraw', 'pcrtype', 'regimen', 'mother_prophylaxis', 'feeding', 'spots', 'comments', 'labcomment', 'parentid', 'rejectedreason', 'reason_for_repeat', 'interpretation', 'result', 'worksheet_id', 'hei_validation', 'enrollment_ccc_no', 'enrollment_status', 'referredfromsite', 'otherreason', 'flag', 'run', 'repeatt', 'eqa', 'approvedby', 'approvedby2', 'datecollected', 'datetested', 'datemodified', 'dateapproved', 'dateapproved2', 'tat1', 'tat2', 'tat3', 'tat4', 'previous_positive', 'synched', 'datesynched', 'mother_last_result', 'mother_age' ], 
        ];
    }

    public static function viralsamples_arrays()
    {
        return [

            'batch' => ['original_batch_id' , 'highpriority', 'input_complete', 'batch_complete', 'site_entry', 'sent_email', 'printedby', 'user_id', 'lab_id', 'facility_id', 'datedispatchedfromfacility', 'datereceived', 'datebatchprinted', 'datedispatched', 'dateindividualresultprinted'],

            'patient' => ['original_patient_id', 'patient', 'sex', 'patient_name', 'facility_id', 'caregiver_phone', 'patient', 'dob', 'initiation_date'],

            'sample' => ['id', 'original_sample_id', 'amrs_location', 'provider_identifier', 'order_no', 'vl_test_request_no', 'receivedstatus', 'age', 'age_category', 'justification', 'other_justification', 'sampletype', 'prophylaxis', 'regimenline', 'pmtct', 'dilutionfactor', 'dilutiontype', 'comments', 'labcomment', 'parentid', 'rejectedreason', 'reason_for_repeat', 'interpretation', 'result', 'rcategory', 'units', 'worksheet_id', 'flag', 'run', 'repeatt', 'approvedby', 'approvedby2', 'datecollected', 'datetested', 'datemodified', 'dateapproved', 'dateapproved2', 'tat1', 'tat2', 'tat3', 'tat4', 'previous_nonsuppressed', 'synched', 'datesynched' ],
            
        ];
    }
}
