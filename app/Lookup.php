<?php

namespace App;

use Illuminate\Support\Facades\Cache;
use DB;
use Carbon\Carbon;
use Exception;

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

    public static function previous_dob($class_name=null, $patient=null, $facility_id=null, $column='dob')
    {
        $rows = $class_name::where(['patient' => $patient, 'facility_id' => $facility_id])
                    ->whereNotIn($column, ['0000-00-00', ''])
                    ->whereNotNull($column)
                    ->get();
        foreach ($rows as $key => $row) {
            $date_field = $row->$column ?? null;
            $val = self::clean_date($date_field);
            if($val) return $val;
        }
        return null;
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
                        ->first();
            if($row){
                $mydate = self::clean_date($row->datecollected);
                if(!$mydate) return null;

                if($class_name == "App\OldModels\ViralsampleView"){ 
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

    public static function calculate_mother_dob($date_collected, $age = null)
    {
        if(!$age) return null;
        $dc = Carbon::parse( $date_collected );
        $dc->subYears($age);
        return $dc->toDateString();
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
                        ->whereRaw("(gender = 'M' or gender = 'F')")->first();
            if($row) return self::resolve_gender($row->gender);
            return 3;
        }
    }

    public static function clean_date($mydate)
    {
        // $mydate = trim($mydate);
        $mydate = preg_replace("/[^<0-9-\/]/", "", $mydate);
        if(!$mydate || $mydate == '0000-00-00' || $mydate == '(NULL)') return null;

        try {
            $my = Carbon::parse($mydate);
            return $my->toDateString();
        } catch (Exception $e) {
            try {
                $my = Carbon::createFromFormat('d/m/Y', $mydate);
                return $my->toDateString();                
            } catch (Exception $exp) {
                return null;
            }
            return null;
        }
    }


    public static function eidsamples_arrays()
    {
        return [

            'batch' => ['original_batch_id' , 'highpriority', 'input_complete', 'batch_complete', 'site_entry', 'sent_email', 'printedby', 'user_id', 'lab_id', 'facility_id', 'datedispatchedfromfacility', 'datereceived', 'datebatchprinted', 'datedispatched', 'dateindividualresultprinted'],

            'mother' => ['hiv_status', 'facility_id', 'ccc_no', 'mother_dob'],

            'patient' => ['original_patient_id', 'patient', 'patient_name', 'sex', 'facility_id', 'caregiver_phone', 'dob', 'entry_point', 'hei_validation', 'enrollment_ccc_no', 'enrollment_status', 'referredfromsite', 'otherreason', 'dateinitiatedontreatment'],

            'sample' => ['original_sample_id', 'amrs_location', 'provider_identifier', 'order_no', 'sample_type', 'receivedstatus', 'age', 'redraw', 'pcrtype', 'regimen', 'mother_prophylaxis', 'feeding', 'spots', 'comments', 'labcomment', 'parentid', 'rejectedreason', 'reason_for_repeat', 'interpretation', 'result', 'worksheet_id', 'flag', 'run', 'repeatt', 'eqa', 'approvedby', 'approvedby2', 'datecollected', 'datetested', 'datemodified', 'dateapproved', 'dateapproved2', 'tat1', 'tat2', 'tat3', 'tat4', 'previous_positive', 'synched', 'datesynched', 'mother_last_result', 'mother_age' ], 
        ];
    }

    public static function viralsamples_arrays()
    {
        return [

            'batch' => ['original_batch_id' , 'highpriority', 'input_complete', 'batch_complete', 'site_entry', 'sent_email', 'printedby', 'user_id', 'lab_id', 'facility_id', 'datedispatchedfromfacility', 'datereceived', 'datebatchprinted', 'datedispatched', 'dateindividualresultprinted'],

            'patient' => ['original_patient_id', 'patient', 'sex', 'patient_name', 'facility_id', 'caregiver_phone', 'patient', 'dob', 'initiation_date'],

            'sample' => ['original_sample_id', 'amrs_location', 'provider_identifier', 'order_no', 'vl_test_request_no', 'receivedstatus', 'age', 'age_category', 'justification', 'other_justification', 'sampletype', 'prophylaxis', 'regimenline', 'pmtct', 'dilutionfactor', 'dilutiontype', 'comments', 'labcomment', 'parentid', 'rejectedreason', 'reason_for_repeat', 'interpretation', 'result', 'rcategory', 'units', 'worksheet_id', 'flag', 'run', 'repeatt', 'approvedby', 'approvedby2', 'datecollected', 'datetested', 'datemodified', 'dateapproved', 'dateapproved2', 'tat1', 'tat2', 'tat3', 'tat4', 'previous_nonsuppressed', 'synched', 'datesynched' ],
            
        ];
    }

    public static function get_eid_lookups()
    {
        self::cacher();
        return [
            'rejected_reasons' => Cache::get('rejected_reasons'),
            'genders' => Cache::get('genders'),
            'feedings' => Cache::get('feedings'),
            'iprophylaxis' => Cache::get('iprophylaxis'),
            'interventions' => Cache::get('interventions'),
            'entry_points' => Cache::get('entry_points'),
            'results' => Cache::get('results'),
            'received_statuses' => Cache::get('received_statuses'),
            'pcrtypes' => Cache::get('pcr_types'),
        ];
    }

    

    public static function get_viral_lookups()
    {
        self::cacher();
        return [
            'viral_rejected_reasons' => Cache::get('viral_rejected_reasons'),
            'vl_result_guidelines' => Cache::get('vl_result_guidelines'),
            'genders' => Cache::get('genders'),
            'sample_types' => Cache::get('sample_types'),
            'received_statuses' => Cache::get('received_statuses'),
            'prophylaxis' => Cache::get('prophylaxis'),
            'justifications' => Cache::get('justifications'),
            'pmtct_types' => Cache::get('pmtct_types'),
        ];        
    }

    public static function filler($sample, $i)
    {
        $filler =  ['total' => 0, 'non_sup' => 0, 'pregnant' => 0, 'breast_feeding' => 0, 'adolescents' => 0, 'children' => 0, 'adults' => 0, 'no_age' => 0];

        $filler['no'] = $i+1;
        $filler['mfl'] = $sample->facilitycode;
        $filler['facility'] = $sample->facility;
        $filler['county'] = $sample->county;
        $filler['subcounty'] = $sample->subcounty;
        $filler['partner'] = $sample->partner;
        return $filler;
    }


    public static function cacher()
    {
        
        // Common Lookup Data
        // $facilities = DB::table('facilitys')->select('id', 'name', 'facilitycode')->get();
        $amrs_locations = DB::table('amrslocations')->get();
        $genders = DB::table('gender')->where('id', '<', 3)->get();
        $received_statuses = DB::table('receivedstatus')->where('id', '<', 3)->get();

        $languages = [
            '1' => 'English',
            '2' => 'Kiswahili',
        ];

        // Eid Lookup Data
        $rejected_reasons = DB::table('rejectedreasons')->get();
        $feedings = DB::table('feedings')->get();
        $iprophylaxis = DB::table('prophylaxis')->where(['ptype' => 2, 'flag' => 1])->where('rank', '>', 0)->orderBy('rank', 'asc')->get();
        $interventions = DB::table('prophylaxis')->where(['ptype' => 1, 'flag' => 1])->where('rank', '>', 0)->orderBy('rank', 'asc')->get();
        $entry_points = DB::table('entry_points')->get();
        $hiv_statuses = DB::table('results')->whereNotIn('id', [3, 5])->get();
        $pcr_types = DB::table('pcrtype')->get();
        $results = DB::table('results')->get();

        // Viralload Lookup Data
        $viral_rejected_reasons = DB::table('viralrejectedreasons')->get();
        $pmtct_types = DB::table('viralpmtcttype')->get();
        $prophylaxis = DB::table('viralprophylaxis')->orderBy('category', 'asc')->get();
        $justifications = DB::table('viraljustifications')->get();
        $sample_types = DB::table('viralsampletype')->where('flag', 1)->get();
        $regimen_lines = DB::table('viralregimenline')->where('flag', 1)->get();
        $vl_result_guidelines = DB::table('vlresultsguidelines')->get();

        // Drug Resistance Lookup Data
        // $drug_resistance_reasons = DB::table('drug_resistance_reasons')->get();
        // $dr_primers = DB::table('dr_primers')->get();
        // $dr_patient_statuses = DB::table('dr_patient_statuses')->get();


        $partners = DB::table('partners')->get();
        $subcounties = DB::table('districts')->get();

        // Cache::put('facilities', $facilities, 60);
        Cache::put('amrs_locations', $amrs_locations, 60);
        Cache::put('genders', $genders, 60);
        Cache::put('received_statuses', $received_statuses, 60);
        Cache::put('languages', $languages, 60);

        Cache::put('rejected_reasons', $rejected_reasons, 60);
        Cache::put('feedings', $feedings, 60);
        Cache::put('iprophylaxis', $iprophylaxis, 60);
        Cache::put('interventions', $interventions, 60);
        Cache::put('entry_points', $entry_points, 60);
        Cache::put('hiv_statuses', $hiv_statuses, 60);
        Cache::put('pcr_types', $pcr_types, 60);
        Cache::put('results', $results, 60);

        Cache::put('viral_rejected_reasons', $viral_rejected_reasons, 60);
        Cache::put('pmtct_types', $pmtct_types, 60);
        Cache::put('prophylaxis', $prophylaxis, 60);
        Cache::put('interventions', $interventions, 60);
        Cache::put('justifications', $justifications, 60);
        Cache::put('sample_types', $sample_types, 60);
        Cache::put('regimen_lines', $regimen_lines, 60);
        Cache::put('vl_result_guidelines', $vl_result_guidelines, 60);

        // Cache::put('drug_resistance_reasons', $drug_resistance_reasons, 60);
        // Cache::put('dr_primers', $dr_primers, 60);
        // Cache::put('dr_patient_statuses', $dr_patient_statuses, 60);

        Cache::put('partners', $partners, 60);
        Cache::put('subcounties', $subcounties, 60);    
    }

    public static function clear_cache()
    {
        // Cache::forget('facilities');
        Cache::forget('amrs_locations');
        Cache::forget('genders');
        Cache::forget('received_statuses');
        Cache::forget('rejected_reasons');
        Cache::forget('feedings');
        Cache::forget('iprophylaxis');
        Cache::forget('interventions');
        Cache::forget('entry_points');
        Cache::forget('pcr_types');
        Cache::forget('results');
        Cache::forget('viral_rejected_reasons');
        Cache::forget('pmtct_types');
        Cache::forget('prophylaxis');
        Cache::forget('interventions');
        Cache::forget('justifications');
        Cache::forget('sample_types');
        Cache::forget('regimen_lines');
        Cache::forget('vl_result_guidelines');
        // Cache::forget('drug_resistance_reasons');
        // Cache::forget('dr_primers');
        // Cache::forget('dr_patient_statuses');

        Cache::forget('partners');
        Cache::forget('subcounties');
    }

    public static function refresh_cache()
    {
        self::clear_cache();
        self::cacher();
    }
}
