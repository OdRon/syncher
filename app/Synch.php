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
	private static $limit = 500;

	public static function synch_eid()
	{
		$fields = Lookup::samples_arrays();	
		$offset_value = 0;
		while(true)
		{
			$samples = OldSampleView::limit(self::$limit)->offset($offset_value)->get();
			if($samples->isEmpty()) break;

			foreach ($samples as $key => $value) {
				$patient = Patient::existing($value->facility_id, $value->patient)->get()->first();

				if(!$patient){
					$mother = new Mother($value->only($fields['mother']));
					$mother->save();
					$patient = new Patient($value->only($fields['patient']));
					$patient->mother_id = $mother->id;
					$patient->dob = Lookup::calculate_dob($value->datecollected, 0, $value->age);
					$patient->sex = Lookup::resolve_gender($value->gender);
					$patient->save();
				}

				$batch = Batch::existing($value->original_batch_id, $value->lab_id)->get()->first();

				if(!$batch){
					$batch = new Batch($value->only($fields['batch']));
					$batch->save();
				}

				$sample = new Sample($value->only($fields['sample']));
				$sample->batch_id = $batch->id;
				$sample->save();
			}
			$offset_value += self::$limit;
		}
	}


	public static function synch_vl()
	{
		$fields = Lookup::viralsamples_arrays();	
		$offset_value = 0;
		while(true)
		{
			$samples = OldViralsampleView::limit(self::$limit)->offset($offset_value)->get();
			if($samples->isEmpty()) break;

			foreach ($samples as $key => $value) {
				$patient = Viralpatient::existing($value->facility_id, $value->patient)->get()->first();

				if(!$patient){
					$patient = new Viralpatient($value->only($fields['patient']));
					$patient->dob = Lookup::calculate_dob($value->datecollected, $value->age, 0);
					$patient->sex = Lookup::resolve_gender($value->gender);
					$patient->save();
				}

				$batch = Viralbatch::existing($value->original_batch_id, $value->lab_id)->get()->first();

				if(!$batch){
					$batch = new Viralbatch($value->only($fields['batch']));
					$batch->save();
				}

				$sample = new Viralsample($value->only($fields['sample']));
				$sample->batch_id = $batch->id;
				$sample->save();
			}
			$offset_value += self::$limit;
		}
	}
}
