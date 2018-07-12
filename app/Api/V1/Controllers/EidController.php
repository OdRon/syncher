<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Api\V1\Requests\BlankRequest;

use App\Misc;
use App\Batch;
use App\Patient;
use App\Sample;
use App\Mother;
use App\Worksheet;

class EidController extends Controller
{

    public function synch_patients(BlankRequest $request)
    {
        $patients_array = [];
        $mothers_array = [];

        $patients = json_decode($request->input('patients'));

        foreach ($patients as $key => $value) {
            $patient = Patient::existing($value->facility_id, $value->patient)->get()->first();
            if(!$patient) continue;
            $patient->original_patient_id = $value->id;
            $patient->save();
            $patients_array[] = $patient->toArray();
            // $patients_array[] = ['original_id' => $patient->original_patient_id, 'national_patient_id' => $patient->id ];

            $mother = $patient->mother;
            if(!$mother) continue;
            $mother->original_mother_id = $value->id;
            $mother->save();
            $mothers_array[] = $mother->toArray();
            // $mothers_array[] = ['original_id' => $mother->original_mother_id, 'national_mother_id' => $mother->id ];
        }

        return response()->json([
            'status' => 'ok',
            'patients' => $patients_array,
            'mothers' => $mothers_array,
        ], 200);
    }

    public function synch_batches(BlankRequest $request)
    {
        $batches_array = [];
        $samples_array = [];
        $batches = json_decode($request->input('batches'));

        foreach ($batches as $key => $value) {
            $batch = Batch::existing($value->id, $value->lab_id)->get()->first();
            if(!$batch) continue;

            $batches_array[] = ['original_id' => $batch->original_batch_id, 'national_batch_id' => $batch->id ];

            foreach ($value->sample as $key2 => $value2) {
                $sample = Sample::where(['original_sample_id' => $value2->id, 'batch_id' => $batch->id])->get()->first();
                if(!$sample) continue;
                $samples_array[] = ['original_id' => $sample->original_sample_id, 'national_sample_id' => $sample->id ];
            }

        }
        return response()->json([
            'status' => 'ok',
            'batches' => $batches_array,
            'samples' => $samples_array,
        ], 200);
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

    public function worksheets(BlankRequest $request)
    {
        $worksheets_array = [];
        $worksheets = json_decode($request->input('worksheets'));

        foreach ($worksheets as $key => $value) {
            $worksheet = new Worksheet;
            $worksheet->fill(get_object_vars($value));
            $worksheet->original_worksheet_id = $worksheet->id;
            unset($worksheet->id);
            unset($worksheet->national_worksheet_id);
            $worksheet->save();
            $worksheets_array[] = ['original_id' => $worksheet->original_worksheet_id, 'national_worksheet_id' => $worksheet->id ];
        }

        return response()->json([
            'status' => 'ok',
            'worksheets' => $worksheets_array,
        ], 201);
    }

    public function update_patients(BlankRequest $request){
        return $this->update_dash($request, Patient::class, 'patients', 'national_patient_id', 'original_patient_id');
    }

    public function update_mothers(BlankRequest $request){
        return $this->update_dash($request, Mother::class, 'mothers', 'national_mother_id', 'original_mother_id');
    }

    public function update_samples(BlankRequest $request){
        return $this->update_dash($request, Sample::class, 'samples', 'national_sample_id', 'original_sample_id');
    }

    public function update_worksheets(BlankRequest $request){
        return $this->update_dash($request, Worksheet::class, 'worksheets', 'national_worksheet_id', 'original_worksheet_id');
    }

    public function update_dash(BlankRequest $request, $update_class, $input, $nat_column, $original_column)
    {
        $models_array = [];
        $models = json_decode($request->input($input));
        $lab_id = json_decode($request->input('lab_id'));

        foreach ($data as $key => $value) {
            if($value->$nat_column){
                $new_model = $update_class::find($value->$nat_column);
            }else{
                $new_model = $update_class::locate($value)->get()->first();
            }

            if(!$new_model) continue;

            $new_model->fill(get_object_vars($value));
            $new_model->$original_column = $new_model->id;
            unset($new_model->id);
            unset($new_model->$nat_column);
            $new_model->save();
            $models_array[] = ['original_id' => $new_model->$original_column, $nat_column => $new_model->id ];
        }

        return response()->json([
            'status' => 'ok',
            $input => $models_array,
        ], 201);        
    }

    public function delete_dash(BlankRequest $request, $update_class, $input, $nat_column, $original_column)
    {
        $models_array = [];
        $models = json_decode($request->input($input));
        $lab_id = json_decode($request->input('lab_id'));

        foreach ($data as $key => $value) {
            if($value->$nat_column){
                $new_model = $update_class::find($value->$nat_column);
            }else{
                $new_model = $update_class::locate($value)->get()->first();
            }

            if(!$new_model) continue;
            
            $models_array[] = ['original_id' => $new_model->$original_column, $nat_column => $new_model->id];
            $new_model->delete();
        }

        return response()->json([
            'status' => 'ok',
            $input => $models_array,
        ], 201);        
    }

}
