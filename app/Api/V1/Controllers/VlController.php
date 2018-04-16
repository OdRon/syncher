<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Api\V1\Requests\BlankRequest;

use App\Misc;
use App\Viralbatch;
use App\Viralpatient;
use App\Viralsample;

class VlController extends Controller
{

    public function patients(BlankRequest $request)
    {
        $patients_array = [];
        $patients = json_decode($request->input('patients'));

        foreach ($patients as $key => $value) {
            $patient = new Viralpatient;
            $patient->fill(get_object_vars($value));
            $patient->original_patient_id = $patient->id;
            unset($patient->id);
            unset($patient->national_patient_id);
            $patient->save();
            $patients_array[] = ['original_id' => $patient->original_patient_id, 'national_patient_id' => $patient->id ];
        }

        return response()->json([
            'status' => 'ok',
            'patients' => $patients_array,
        ], 201);
    }

    public function batches(BlankRequest $request)
    {
        $batches_array = [];
        $samples_array = [];
        
        $batches = json_decode($request->input('batches'));

        foreach ($batches as $key => $value) {
            $batch = new Viralbatch;
            $temp = $value;
            unset($temp->sample);
            $batch->fill(get_object_vars($temp));
            $batch->original_batch_id = $batch->id;
            unset($batch->id);
            unset($batch->national_batch_id);
            $batch->save();

            $batches_array[] = ['original_id' => $batch->original_batch_id, 'national_batch_id' => $batch->id ];

            foreach ($value->sample as $key2 => $value2) {
                if($value2->parentid != 0) continue;
                $sample = new Viralsample;
                $sample->fill(get_object_vars($value2));
                $sample->original_sample_id = $sample->id;
                $sample->patient_id = $value2->patient->national_patient_id;
                unset($sample->id);
                unset($sample->patient);
                unset($sample->national_sample_id);

                $sample->batch_id = $batch->id;
                $sample->save();
                
                $samples_array[] = ['original_id' => $sample->original_sample_id, 'national_sample_id' => $sample->id ];                
            }

            foreach ($value->sample as $key2 => $value2) {
                if($value2->parentid == 0) continue;
                $sample = new Viralsample;
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
