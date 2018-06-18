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

class Synch
{
	private static $limit = 10000;

	public static function synch_eid()
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


	public static function synch_vl()
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
}
