<?php

namespace App;

use App\Lookup;

use App\OldSampleView;
use App\Mother;
use App\Patient;
use App\Batch;
use App\Sample;

use App\OldViralsampleView;
use App\Viralpatient;
use App\Viralbatch;
use App\Viralsample;

class Copier
{
	private static $limit = 10000;

	public static function copy_eid()
	{
		$start = Sample::max('id');
		ini_set("memory_limit", "-1");
		$fields = Lookup::samples_arrays();	
        $sample_date_array = ['datecollected', 'datetested', 'datemodified', 'dateapproved', 'dateapproved2'];
        $batch_date_array = ['datedispatchedfromfacility', 'datereceived', 'datedispatched', 'dateindividualresultprinted', 'datebatchprinted'];

		$offset_value = 0;
		while(true)
		{
			$samples = OldSampleView::when($start, function($query) use ($start){
				return $query->where('id', '>', $start);
			})->limit(self::$limit)->offset($offset_value)->get();
			if($samples->isEmpty()) break;
			

			foreach ($samples as $key => $value) {
				$patient = Patient::existing($value->facility_id, $value->patient)->get()->first();

				if(!$patient){
					$mother = new Mother($value->only($fields['mother']));
					$mother->save();
					$patient = new Patient($value->only($fields['patient']));
					$patient->mother_id = $mother->id;
					$patient->dob = Lookup::calculate_dob($value->datecollected, 0, $value->age, OldSampleView::class, $value->patient, $value->facility_id);
					$patient->sex = Lookup::resolve_gender($value->gender, OldSampleView::class, $value->patient, $value->facility_id);
					$enrollment_data = self::get_enrollment_data($value->patient, $value->facility_id);
					if($enrollment_data) $patient->fill($enrollment_data);
					// $patient->ccc_no = $value->enrollment_ccc_no;
					$patient->save();
				}
				
				$value->original_batch_id = self::set_batch_id($value->original_batch_id);
				$batch = Batch::existing($value->original_batch_id, $value->lab_id)->get()->first();

				if(!$batch){
					$batch = new Batch($value->only($fields['batch']));
                    foreach ($batch_date_array as $date_field) {
                        $batch->$date_field = Lookup::clean_date($batch->$date_field);
                    }
					$batch->save();
				}

				$sample = new Sample($value->only($fields['sample']));
                foreach ($sample_date_array as $date_field) {
                    $sample->$date_field = Lookup::clean_date($sample->$date_field);
                }

				$sample->batch_id = $batch->id;
				$sample->patient_id = $patient->id;

                if($sample->age == 0 && $batch->datecollected && $patient->dob){
                    $sample->age = Lookup::calculate_age($batch->datecollected, $patient->dob);
                }

				$sample->save();
			}
			$offset_value += self::$limit;
			echo "Completed eid {$offset_value} at " . date('d/m/Y h:i:s a', time()). "\n";
		}
	}


	public static function copy_vl()
	{
		$start = Viralsample::max('id');
		ini_set("memory_limit", "-1");
		$fields = Lookup::viralsamples_arrays();
        $sample_date_array = ['datecollected', 'datetested', 'datemodified', 'dateapproved', 'dateapproved2'];
        $batch_date_array = ['datedispatchedfromfacility', 'datereceived', 'datedispatched', 'dateindividualresultprinted', 'datebatchprinted'];	
		$offset_value = 0;
		while(true)
		{
			$samples = OldViralsampleView::when($start, function($query) use ($start){
				return $query->where('id', '>', $start);
			})->limit(self::$limit)->offset($offset_value)->get();
			if($samples->isEmpty()) break;

			foreach ($samples as $key => $value) {
				$patient = Viralpatient::existing($value->facility_id, $value->patient)->get()->first();

				if(!$patient){
					$patient = new Viralpatient($value->only($fields['patient']));
					$patient->dob = Lookup::calculate_dob($value->datecollected, $value->age, 0, OldViralsampleView::class, $value->patient, $value->facility_id);
					$patient->sex = Lookup::resolve_gender($value->gender, OldViralsampleView::class, $value->patient, $value->facility_id);
					$patient->save();
				}

				$value->original_batch_id = self::set_batch_id($value->original_batch_id);
				$batch = Viralbatch::existing($value->original_batch_id, $value->lab_id)->get()->first();

				if(!$batch){
					$batch = new Viralbatch($value->only($fields['batch']));
                    foreach ($batch_date_array as $date_field) {
                        $batch->$date_field = Lookup::clean_date($batch->$date_field);
                    }
					$batch->save();
				}

				$sample = new Viralsample($value->only($fields['sample']));
                foreach ($sample_date_array as $date_field) {
                    $sample->$date_field = Lookup::clean_date($sample->$date_field);
                }
				$sample->batch_id = $batch->id;
				$sample->patient_id = $patient->id;

                if($sample->age == 0 && $batch->datecollected && $patient->dob){
                    $sample->age = Lookup::calculate_viralage($batch->datecollected, $patient->dob);
                }

				$sample->save();
			}
			$offset_value += self::$limit;
			echo "Completed vl {$offset_value} at " . date('d/m/Y h:i:s a', time()). "\n";
		}
	}

    private static function set_batch_id($batch_id)
    {
        if($batch_id == floor($batch_id)) return $batch_id;
        return (floor($batch_id) + 0.5);
    }

    public static function get_enrollment_data($patient, $facility_id)
    {
    	$sample = OldSampleView::where('patient', $patient)
    				->where('facility_id', $facility_id)
    				->where('hei_validation', '>', 0)
    				->get()
    				->first();

    	if($sample){
    		return [
    			'hei_validation' => $sample->hei_validation,
    			'enrollment_ccc_no' => $sample->enrollment_ccc_no,
    			'enrollment_status' => $sample->enrollment_status,
    			'referredfromsite' => $sample->referredfromsite,
    			'otherreason' => $sample->otherreason,
    		];
    	}
    	else{
    		return false;
    	}
    }

    public static function assign_patient_statuses()
    {
    	print_r("==> Getting patient data at " . date('d/m/Y h:i:s a', time()). "\n");
    	$patients = \App\Patient::whereNull('hiv_status')->get();
    	ini_set("memory_limit", "-1");
        
        print_r("==> Started assigning patients` statuses at " . date('d/m/Y h:i:s a', time()). "\n");
        foreach ($patients as $key => $patient) {
            $samples = Sample::select('samples.id','patient_id','parentid','samples.result as result_id','results.name as result','datetested')->join('results', 'results.id','=','samples.result')
				->where('patient_id', '=', $patient->id)->orderBy('datetested','asc')->get();
            if ($samples->count() == 1){
            	$sample = $samples->first();
	            if ($sample->result < 3) {
	                $patient->hiv_status = $sample->result;
	                $patient->save();
	                // print_r("\tPatient $patient->patient save completed at " . date('d/m/Y h:i:s a', time()). "\n");
                }
            } else {
            	$data = [];
            	foreach ($samples as $key => $sample) {
                    $data[] = ['id'=>$sample->id,'patient_id'=>$sample->patient_id,'result_id'=>$sample->result_id,'result'=>$sample->result,'datetested'=>$sample->datetested];
                }
                if (!empty($data)) {
	                $length = sizeof($data)-1;
	                $arr = $data[$length];
	                if ($arr['result_id'] > 2) {
	                	$length -= $length;
	                	$arr = $data[$length];
	                	$status = $arr['result_id'];
	                } else {
	                	$status = $arr['result_id'];
	                }
	                $patient->hiv_status = $status;
	                $patient->save();
	                // print_r("\tPatient $patient->patient save completed at " . date('d/m/Y h:i:s a', time()). "\n");
	            }
            }
            // break;
        }
        // dd($data);
        return "==> Completed assigning patients` statuses at " . date('d/m/Y h:i:s a', time()). "\n";
    }
}
