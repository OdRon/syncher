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
        $lab_id = json_decode($request->input('lab_id'));

        foreach ($batches as $key => $value) {
            $batch = Batch::where(['original_batch_id' => $value->id, 'lab_id' => $value->lab_id])->first();
            if(!$batch) $batch = new Batch;
            $samples = $value->sample;
            $batch->original_batch_id = $value->id;            
            $temp = $value;
            unset($temp->sample);
            unset($temp->id);            
            $batch->fill(get_object_vars($temp));
            unset($batch->national_batch_id);
            $batch->save();

            $batches_array[] = ['original_id' => $batch->original_batch_id, 'national_batch_id' => $batch->id ];

            foreach ($samples as $key2 => $value2) {
                // if($value2->parentid != 0) continue;

                // $pat = json_decode($value2->patient);

                $sample = new Sample;
                $sample->fill(get_object_vars($value2));
                $sample->original_sample_id = $sample->id;
                $sample->patient_id = $value2->patient->national_patient_id;
                unset($sample->id);
                unset($sample->patient);
                unset($sample->national_sample_id);
                unset($sample->sample_received_by);

                // if($sample->parentid != 0) $sample->parentid = Misc::get_new_id($samples_array, $sample->parentid);
                    
                $sample->batch_id = $batch->id;
                $sample->save();

                $samples_array[] = ['original_id' => $sample->original_sample_id, 'national_sample_id' => $sample->id ];                
            }

            // Parent ID will be the sample ID at the lab instead of the national sample ID
            // foreach ($value->sample as $key2 => $value2) {
            //     if($value2->parentid == 0) continue;

            //     $sample = new Sample;
            //     $sample->fill(get_object_vars($value2));
            //     $sample->original_sample_id = $sample->id;
            //     $sample->patient_id = $value2->patient->national_patient_id;
            //     unset($sample->id);
            //     unset($sample->patient);
            //     unset($sample->national_sample_id);

            //     $sample->parentid = Misc::get_new_id($samples_array, $sample->parentid);    
            //     $sample->batch_id = $batch->id;
            //     $sample->save();

            //     $samples_array[] = ['original_id' => $sample->original_sample_id, 'national_sample_id' => $sample->id ];                
            // }

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

    public function update_batches(BlankRequest $request){
        return $this->update_dash($request, Batch::class, 'batches', 'national_batch_id', 'original_batch_id');
    }

    public function update_samples(BlankRequest $request){
        return $this->update_dash($request, Sample::class, 'samples', 'national_sample_id', 'original_sample_id');
    }

    public function update_worksheets(BlankRequest $request){
        return $this->update_dash($request, Worksheet::class, 'worksheets', 'national_worksheet_id', 'original_worksheet_id');
    }

    // Change foreign keys e.g. batch_id, patient_id
    public function update_dash(BlankRequest $request, $update_class, $input, $nat_column, $original_column)
    {
        $models_array = [];
        $errors_array = [];
        $models = json_decode($request->input($input));
        $lab_id = json_decode($request->input('lab_id'));

        foreach ($models as $key => $value) {
            if($value->$nat_column){
                $new_model = $update_class::find($value->$nat_column);
            }else{
                if($input == 'samples'){
                    $s = \App\SampleView::locate($value, $lab_id)->first();
                    if(!$s){
                        $errors_array[] = $value;
                        continue;
                    }
                    $new_model = $update_class::find($s->id);
                }else{
                    $new_model = $update_class::locate($value)->get()->first();
                }
            }

            if(!$new_model){
                $errors_array[] = $value;
                continue;
            }

            $update_data = get_object_vars($value);
            unset($update_data['id']);
            unset($update_data['created_at']);
            unset($update_data['updated_at']);

            if($input == 'patients'){
                unset($update_data['hei_validation']);
                unset($update_data['enrollment_ccc_no']);
                unset($update_data['enrollment_status']);
                unset($update_data['referredfromsite']);
                unset($update_data['otherreason']);

                unset($update_data['mother_id']);
            }

            if($input == 'samples'){
                $original_batch = $value->batch;
                $original_patient = $value->patient;
                if($original_batch) $update_data['batch_id'] = $original_batch->national_batch_id;
                else{
                    unset($update_data['batch_id']);
                }
                $update_data['patient_id'] = $original_patient->national_patient_id;

                unset($update_data['batch']);
                unset($update_data['patient']);
                unset($update_data['sample_received_by']);


                // $batch = $new_model->batch;
                // if($batch->original_batch_id == $new_model->batch_id) unset($update_data['batch_id']);
                // else{
                //     $b = Batch::existing($update_data['batch_id'], $lab_id)->first();
                //     $update_data['batch_id'] = $b->id;
                // }
                // $patient = $new_model->patient;
                // if($patient->original_patient_id == $new_model->patient_id) unset($update_data['patient_id']);
                // else{

                // }

            }

            $new_model->fill($update_data);
            $new_model->$original_column = $value->id;
            $new_model->synched = 1;
            unset($new_model->$nat_column);

            $new_model->save();
            $models_array[] = ['original_id' => $new_model->$original_column, $nat_column => $new_model->id ];
        }

        if(count($errors_array) == 0) $errors_array = null;

        return response()->json([
            'status' => 'ok',
            $input => $models_array,
            'errors_array' => $errors_array,
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
                if($input == 'samples'){
                    $s = \App\SampleView::locate($value, $lab_id)->first();
                    $new_model = $update_class::find($s->id);
                }else{
                    $new_model = $update_class::locate($value)->get()->first();
                }
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
