<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Api\V1\Requests\BlankRequest;

use App\Misc;
use App\Batch;
use App\Patient;
use App\Sample;
use App\Mother;

class EidController extends Controller
{

    public function mothers(BlankRequest $request)
    {
      $mothers_array = [];
      $mothers = $request->input('mothers');

      foreach ($mothers as $key => $value) {

      }
    }

    public function patients(BlankRequest $request)
    {
      $patients_array = [];
      $mothers_array = [];
      $patients = json_decode($request->input('patients'));

      foreach ($patients as $key => $value) {
        $mother = new Mother;
        $mother_data = get_object_vars($value->mother);
        $mother->fill($mother_data);
        $mother->original_mother_id = $mother->id;
        unset($mother->id);
        unset($mother->national_mother_id);
        $mother->save();
        $mothers_array[] = ['original_id' => $mother->original_mother_id, 'national_mother_id' => $mother->id ];

        unset($value->mother);
        $patient = new Patient;
        $patient->fill(get_object_vars($value));
        $patient->mother_id = $mother->id;
        $patient->original_patient_id = $patient->id;
        unset($patient->id);
        unset($patient->national_patient_id);
        $patient->save();
        $patients_array[] = ['original_id' => $patient->original_patient_id, 'national_patient_id' => $patient->id ];
      }

        return response()->json([
          'status' => 'ok',
          'patients' => $patients_array,
          'mothers' => $mothers_array,
        ], 201);
    }

    public function batches(BlankRequest $request)
    {
        $batches_array = [];
        $samples_array = [];
        
        $batches = json_decode($request->input('batches'));

        foreach ($batches as $key => $value) {
            $batch = new Batch;
            $batch->fill(get_object_vars($value));
            $batch->original_batch_id = $batch->id;
            unset($batch->id);
            unset($batch->national_batch_id);
            unset($batch->sample);
            $batch->save();

            $batches_array[] = ['original_id' => $batch->original_batch_id, 'national_batch_id' => $batch->id ];

            foreach ($value->sample as $key2 => $value2) {
                if($value2->parentid != 0) continue;

                // $pat = json_decode($value2->patient);

                $sample = new Sample;
                $sample->fill(get_object_vars($value2));
                $sample->original_sample_id = $sample->id;
                $sample->patient_id = $value2->patient->national_patient_id;
                unset($sample->id);
                unset($sample->patient);
                unset($sample->national_sample_id);

                // if($sample->parentid != 0) $sample->parentid = Misc::get_new_id($samples_array, $sample->parentid);
                    
                $sample->batch_id = $batch->id;
                $sample->save();

                $samples_array[] = ['original_id' => $sample->original_sample_id, 'national_sample_id' => $sample->id ];                
            }

            foreach ($value->sample as $key2 => $value2) {
                if($value2->parentid == 0) continue;

                $sample = new Sample;
                $sample->fill(get_object_vars($value2));
                $sample->original_sample_id = $sample->id;
                $sample->patient_id = $value2->patient->national_patient_id;
                unset($sample->id);
                unset($sample->patient);
                unset($sample->national_sample_id);

                $sample->parentid = Misc::get_new_id($samples_array, $sample->parentid);    
                $sample->batch_id = $batch->id;
                $sample->save();

                $samples_array[] = ['original_id' => $sample->original_sample_id, 'national_sample_id' => $sample->id ];                
            }

        }
        return response()->json([
            'status' => 'ok',
            'batches' => $batches_array,
            'samples' => $samples_array,
        ], 201);
    }

}
