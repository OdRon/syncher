<?php

namespace App;

use DB;
use App\Lookup;
use App\Bookmark;

use App\OldModels\WorksheetView;
use App\OldModels\ViralworksheetView;

use App\OldModels\SampleView;
use App\OldModels\ViralsampleView;
use App\OldModels\User as OldUser;

use App\Mother;
use App\Patient;
use App\Batch;
use App\Sample;
use App\User;

use App\Viralpatient;
use App\Viralbatch;
use App\Viralsample;


use App\Worksheet;
use App\Viralworksheet;

class Copier
{
	private static $limit = 10000;

    public static function return_dateinitiated()
    {
        ini_set("memory_limit", "-1");
        $offset =0;

        while(true){
            $rows = SampleView::select('patient', 'facility_id', 'dateinitiatedontreatment')
                                ->whereNotNull('dateinitiatedontreatment')
                                ->whereNotIn('dateinitiatedontreatment', ['0000-00-00', ''])
                                ->limit(5000)
                                ->offset($offset)
                                ->get();
            if($rows->isEmpty()) break;

            foreach ($rows as $key => $row) {
                $d = Lookup::clean_date($row->dateinitiatedontreatment);
                if(!$d) continue;

                $patient = Patient::existing($row->facility_id, $row->patient)->first();
                if(!$patient) continue;

                if($patient->dateinitiatedontreatment && $patient->dateinitiatedontreatment != '0000-00-00') continue;
                $patient->dateinitiatedontreatment = $d;
                $patient->save();

            }
            $offset += 5000;
        }

    }

    public static function return_vl_dateinitiated()
    {
        ini_set("memory_limit", "-1");
        $offset =0;

        while(true){
            $rows = ViralsampleView::select('patient', 'facility_id', 'initiation_date')
                                ->whereNotNull('initiation_date')
                                ->whereNotIn('initiation_date', ['0000-00-00', ''])
                                ->limit(5000)
                                ->offset($offset)
                                ->get();
            if($rows->isEmpty()) break;

            foreach ($rows as $key => $row) {
                $d = Lookup::clean_date($row->initiation_date);
                if(!$d) continue;

                $patient = Viralpatient::existing($row->facility_id, $row->patient)->first();
                if(!$patient) continue;

                if($patient->initiation_date && $patient->initiation_date != '0000-00-00') continue;
                $patient->initiation_date = $d;
                $patient->save();

            }
            $offset += 5000;
        }

    }

    public static function delete_duplicates_vl()
    {
        ini_set('memory_limit', '-1');
        $duplicates = Viralsample::selectRaw("old_id, count(old_id) as my_count")
                    ->groupBy('old_id')
                    ->having('my_count', 165)
                    ->get();

        foreach ($duplicates as $duplicate) {
            $samples = Viralsample::where('old_id', $duplicate->old_id)->get();
            $first = true;

            foreach ($samples as $sample) {
                if($first){
                    $first=false;
                    continue;
                }

                $sample->delete();
            }
        }
    }



	public static function copy_eid()
	{
        $bookmark = Bookmark::find(1);
		$start = $bookmark->samples ?? 0;
		ini_set("memory_limit", "-1");
		$fields = Lookup::samples_arrays();	
        $sample_date_array = ['datecollected', 'datetested', 'datemodified', 'dateapproved', 'dateapproved2'];
        $batch_date_array = ['datedispatchedfromfacility', 'datereceived', 'datedispatched', 'dateindividualresultprinted', 'datebatchprinted'];

		$offset_value = 0;
		while(true)
		{
			$samples = SampleView::when($start, function($query) use ($start){
				return $query->where('id', '>', $start);
			})->limit(self::$limit)->offset($offset_value)->get();
			if($samples->isEmpty()) break;
			

			foreach ($samples as $key => $value) {
				$patient = Patient::existing($value->facility_id, $value->patient)->get()->first();

				if(!$patient){
					$mother = new Mother($value->only($fields['mother']));
                    if($value->mother_age) $mother->mother_dob = Lookup::calculate_dob($value->datecollected, $value->mother_age, 0);
					$mother->save();
					$patient = new Patient($value->only($fields['patient']));
					$patient->mother_id = $mother->id;

                    if($patient->dob) $patient->dob = Lookup::clean_date($patient->dob);

                    if(!$patient->dob) $patient->dob = Lookup::previous_dob(SampleView::class, $value->patient, $value->facility_id);

                    if(!$patient->dob) $patient->dob = Lookup::calculate_dob($value->datecollected, 0, $value->age, SampleView::class, $value->patient, $value->facility_id);


					$patient->sex = Lookup::resolve_gender($value->gender, SampleView::class, $value->patient, $value->facility_id);
					$enrollment_data = self::get_enrollment_data($value->patient, $value->facility_id);
					if($enrollment_data) $patient->fill($enrollment_data);
					// $patient->ccc_no = $value->enrollment_ccc_no;
					$patient->save();
				}else{
                    $dob = Lookup::clean_date($value->dob);
                    $dateinitiatedontreatment = Lookup::clean_date($value->dateinitiatedontreatment);
                    if(!$dateinitiatedontreatment) $dateinitiatedontreatment = Lookup::previous_dob(SampleView::class, $value->patient, $value->facility_id, 'dateinitiatedontreatment');
                    $sex = Lookup::resolve_gender($value->gender);
                    if($dob) $patient->dob = $dob;
                    if(!$patient->dob) $patient->dob = Lookup::calculate_dob($value->datecollected, 0, $value->age);
                    if($dateinitiatedontreatment) $patient->dateinitiatedontreatment = $dateinitiatedontreatment;

                    if($patient->sex == 3 && $sex != 3) $patient->sex = $sex;
                    $patient->save();

                    $mother = $patient->mother;
                    $mother->fill($value->only($fields['mother']));
                    if($value->mother_age) $mother->mother_dob = Lookup::calculate_dob($value->datecollected, $value->mother_age, 0);
                    $mother->save();
                }
				
				$value->original_batch_id = self::set_batch_id($value->original_batch_id);
                $batch = null;
                if($value->original_batch_id != 0){
    				$batch = Batch::existing($value->original_batch_id, $value->lab_id)->get()->first();
                }

				if(!$batch){
					$batch = new Batch($value->only($fields['batch']));
                    foreach ($batch_date_array as $date_field) {
                        $batch->$date_field = Lookup::clean_date($batch->$date_field);
                        if($batch->$date_field == '1970-01-01') $batch->$date_field = null;
                    }
                    if(!$batch->received_by) $batch->received_by = $value->user_id;
                    $batch->entered_by = $value->user_id;
					$batch->save();
				}

				$sample = new Sample($value->only($fields['sample']));
                foreach ($sample_date_array as $date_field) {
                    $sample->$date_field = Lookup::clean_date($sample->$date_field);
                    if($sample->$date_field == '1970-01-01') $sample->$date_field = null;
                }

				$sample->batch_id = $batch->id;
				$sample->patient_id = $patient->id;

                if(!$sample->age && $batch->datecollected && $patient->dob){
                    $sample->age = Lookup::calculate_age($batch->datecollected, $patient->dob);
                }

                if($sample->worksheet_id == 0) $sample->worksheet_id = null;
                if($sample->receivedstatus == 0) $sample->receivedstatus = null;
                if($sample->result == '') $sample->result = null;

                $sample->old_id = $value->id;
				$sample->save();
			}
			$offset_value += self::$limit;
			echo "Completed eid {$offset_value} at " . date('d/m/Y h:i:s a', time()). "\n";
		}
        $bookmark->samples = $value->id;
        $bookmark->save();

	}




	public static function copy_vl()
	{
        $bookmark = Bookmark::find(1);
        $start = $bookmark->viralsamples ?? 0;
		ini_set("memory_limit", "-1");
		$fields = Lookup::viralsamples_arrays();
        $sample_date_array = ['datecollected', 'datetested', 'datemodified', 'dateapproved', 'dateapproved2'];
        $batch_date_array = ['datedispatchedfromfacility', 'datereceived', 'datedispatched', 'dateindividualresultprinted', 'datebatchprinted'];	
		$offset_value = 0;
		while(true)
		{
			$samples = ViralsampleView::when($start, function($query) use ($start){
				return $query->where('id', '>', $start);
			})->limit(self::$limit)->offset($offset_value)->get();
			if($samples->isEmpty()) break;

			foreach ($samples as $key => $value) {
				$patient = Viralpatient::existing($value->facility_id, $value->patient)->get()->first();

				if(!$patient){
					$patient = new Viralpatient($value->only($fields['patient']));

                    if($patient->dob) $patient->dob = Lookup::clean_date($patient->dob);

                    if(!$patient->dob) $patient->dob = Lookup::previous_dob(ViralsampleView::class, $value->patient, $value->facility_id);
                    if(!$patient->dob) $patient->dob = Lookup::calculate_dob($value->datecollected, $value->age, 0, ViralsampleView::class, $value->patient, $value->facility_id);

					$patient->sex = Lookup::resolve_gender($value->gender, ViralsampleView::class, $value->patient, $value->facility_id);
					$patient->save();
				}else{
                    $dob = Lookup::clean_date($value->dob);
                    if(!$dob) $dob = Lookup::calculate_dob($value->datecollected, $value->age, 0);
                    if($dob) $patient->dob = $dob;
                    $sex = Lookup::resolve_gender($value->gender);
                    if($sex != 3 && $patient->sex == 3) $patient->sex = $sex;
                    $initiation_date = Lookup::clean_date($value->initiation_date);
                    if($initiation_date) $patient->initiation_date = $initiation_date;
                    $patient->save();
                }

				$value->original_batch_id = self::set_batch_id($value->original_batch_id);
                $batch = null;
                if($value->original_batch_id != 0){
    				$batch = Viralbatch::existing($value->original_batch_id, $value->lab_id)->get()->first();
                }

				if(!$batch){
					$batch = new Viralbatch($value->only($fields['batch']));
                    foreach ($batch_date_array as $date_field) {
                        $batch->$date_field = Lookup::clean_date($batch->$date_field);
                        if($batch->$date_field == '1970-01-01') $batch->$date_field = null;
                    }
                    if(!$batch->received_by) $batch->received_by = $value->user_id;
                    $batch->entered_by = $value->user_id;
					$batch->save();
				}

				$sample = new Viralsample($value->only($fields['sample']));
                foreach ($sample_date_array as $date_field) {
                    $sample->$date_field = Lookup::clean_date($sample->$date_field);
                    if($sample->$date_field == '1970-01-01') $sample->$date_field = null;
                }
				$sample->batch_id = $batch->id;
				$sample->patient_id = $patient->id;

                if(!$sample->age && $batch->datecollected && $patient->dob){
                    $sample->age = Lookup::calculate_viralage($batch->datecollected, $patient->dob);
                }
                
                if($sample->worksheet_id == 0) $sample->worksheet_id = null;
                if($sample->receivedstatus == 0) $sample->receivedstatus = null;
                if($sample->result == '') $sample->result = null;

                $sample->old_id = $value->id;
				$sample->save();
			}
			$offset_value += self::$limit;
			echo "Completed vl {$offset_value} at " . date('d/m/Y h:i:s a', time()). "\n";
		}
        $bookmark->viralsamples = $value->id;
        $bookmark->save();
	}



    public static function copy_worksheet()
    {
        $bookmark = Bookmark::find(1);

        $work_array = [
            'eid' => ['model' => Worksheet::class, 'view' => WorksheetView::class, 'col' => 'worksheets'],
            'vl' => ['model' => Viralworksheet::class, 'view' => ViralworksheetView::class, 'col' => 'viralworksheets'],
        ];

        $date_array = ['kitexpirydate', 'sampleprepexpirydate', 'bulklysisexpirydate', 'controlexpirydate', 'calibratorexpirydate', 'amplificationexpirydate', 'datecut', 'datereviewed', 'datereviewed2', 'datecancelled', 'daterun', 'created_at'];

        ini_set("memory_limit", "-1");

        foreach ($work_array as $key => $value) {
            $model = $value['model'];
            $view = $value['view'];
            $col = $value['col'];

            // $start = $model::max('id');
            $start = $bookmark->$col ?? 0;            

            $offset_value = 0;
            while(true)
            {
                $worksheets = $view::when($start, function($query) use ($start){
                    return $query->where('id', '>', $start);
                })->limit(self::$limit)->offset($offset_value)->get();
                if($worksheets->isEmpty()) break;

                foreach ($worksheets as $worksheet_key => $worksheet) {
                    $duplicate = $worksheet->replicate();
                    $work = new $model;                    
                    $work->fill($duplicate->toArray());
                    foreach ($date_array as $date_field) {
                        $work->$date_field = Lookup::clean_date($worksheet->$date_field);
                    }
                    // $work->id = $worksheet->id;
                    $work->save();
                }
                $offset_value += self::$limit;
                echo "Completed {$key} worksheet {$offset_value} at " . date('d/m/Y h:i:s a', time()). "\n";
            }
            $bookmark->$col = $worksheet->id;
            $bookmark->save();
        }
    }

    public static function set_batch_id($batch_id)
    {
        if($batch_id == floor($batch_id)) return $batch_id;
        return (floor($batch_id) + 0.5);
    }

    public static function get_enrollment_data($patient, $facility_id)
    {
    	$sample = SampleView::where('patient', $patient)
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

    public static function copy_users() {
        $start = 0;
        $offset_value = 0;
        $count = 0;
        
        ini_set("memory_limit", "-1");

        while (true) {
            echo "==> Getting users {$offset_value} - " . self::$limit . " at " . date('d/m/Y h:i:s a', time()). "\n";
            $oldUsers = OldUser::when($start, function($query) use ($start){
                            return $query->where('id', '>', $start);
                        })->limit(self::$limit)->offset($offset_value)->get();
            if($oldUsers->isEmpty()) break;

            echo "==> Begin copying users at " . date('d/m/Y h:i:s a', time()). "\n";
            foreach ($oldUsers as $key => $value) {
                $count++;
                $userCheck = User::where('email', '=', $value->email)->get();
                if($userCheck->isEmpty()){
                    if (!($value->account == 0 || $value->account == 6)) {
                        $newUser = new User();
                        $usertype = DB::table('user_types')->where('old_id', '=', $value->account)->first();
                        $newUser->user_type_id = $usertype->id ?? NULL;
                        $newUser->lab_id = $value->lab ?? 0;
                        $newUser->surname = $value->surname ?? '';
                        $newUser->oname = $value->oname ?? '';
                        $email = trim($value->email);
                        $email = ($email == '' || $email == null) ? $value->username.'@example.com' : $email;
                        $newUser->email = $email;
                        $newUser->username = $value->username ?? $email;
                        $newUser->password = env('MASTER_PASSWORD');
                        $newUser->old_password = $value->password ?? NULL;
                        $newUser->level = $value->partner ?? NULL;
                        $newUser->telephone = $value->telephone ?? NULL;
                        $newUser->save();
                    }
                }
            }
            $offset_value += $count;
            echo "==> Completed copying {$offset_value} users at " . date('d/m/Y h:i:s a', time()). "\n";
            break;
        }
    }

    // public static function deactivate_old_users()
    // {

    //     while (true) {
    //         $count = 0;
    //         $oldUsers = OldUser::where('flag', '=', 0)->get();
    //         if($oldUsers->isEmpty()) break;
    //         echo "==> Started at " . date('d/m/Y h:i:s a', time()). "\n";
    //         foreach ($oldUsers as $key => $value) {
    //             $current = User::where('username', '=', $value->username)->get();
                
    //             if(!$current->isEmpty()) {
    //                 $user = User::find($current->first()->id);
    //                 $user->deleted_at = date('Y-m-d H:i:s');
    //                 $user->save();
    //                 $count++;
    //             }
    //         }
    //         echo "==> Completed updating {$count} users at " . date('d/m/Y h:i:s a', time()). "\n";
    //         break;
    //     }
    // }

    public static function assign_patient_statuses()
    {
    	print_r("==> Getting patient data at " . date('d/m/Y h:i:s a', time()). "\n");
    	ini_set("memory_limit", "-1");
    	$patients = \App\Patient::whereNull('hiv_status')->get();
        
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
