<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Api\V1\Requests\BlankRequest;
use Exception;

use App\Misc;
use App\Viralbatch;
use App\Viralpatient;
use App\Viralsample;
use App\ViralsampleView;
use App\Viralworksheet;

class VlController extends Controller
{


    public function synch_patients(BlankRequest $request)
    {
        $patients_array = [];

        $patients = json_decode($request->input('patients'));

        foreach ($patients as $key => $value) {
            $patient = Viralpatient::existing($value->facility_id, $value->patient)->get()->first();
            // if(!$patient) continue;
            if(!$patient){
                $patient = new Viralpatient;
                $patient->fill(get_object_vars($value));
                $patient->original_patient_id = $patient->id;
                unset($patient->id);
                unset($patient->national_patient_id);
                $patient->save();
            }
            else{
                $patient->original_patient_id = $value->id;
                $patient->save();                
            }
            $patients_array[] = $patient->toArray();
            // $patients_array[] = ['original_id' => $patient->original_patient_id, 'national_patient_id' => $patient->id ];
        }

        return response()->json([
            'status' => 'ok',
            'patients' => $patients_array,
        ], 200);
    }

    public function synch_batches(BlankRequest $request)
    {
        $batches_array = [];
        $samples_array = [];
        $batches = json_decode($request->input('batches'));

        foreach ($batches as $key => $value) {
            $batch = Viralbatch::existing($value->id, $value->lab_id)->get()->first();
            if(!$batch) continue;

            $batches_array[] = ['original_id' => $batch->original_batch_id, 'national_batch_id' => $batch->id ];


            foreach ($value->sample as $key2 => $value2) {
                $sample = Viralsample::where(['original_sample_id' => $value2->id, 'batch_id' => $batch->id])->first();
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

    public function synch_samples(BlankRequest $request)
    {
        $samples_array = [];
        $samples = json_decode($request->input('samples'));

        foreach ($samples as $key => $value) {
            if(!isset($value->batch) || !$value->batch->national_batch_id) continue;
            // $sample = ViralsampleView::where(['original_sample_id' => $value->id, 'batch_id' => $value->batch->national_batch_id])->first();
            $sample = ViralsampleView::where(['original_sample_id' => $value->id, 'lab_id' => $value->batch->lab_id])->first();
            if(!$sample) continue;
            $samples_array[] = ['original_id' => $sample->original_sample_id, 'national_sample_id' => $sample->id ];
        }

        return response()->json([
            'status' => 'ok',
            'samples' => $samples_array,
        ], 200);
    }

    public function patients(BlankRequest $request)
    {
        $patients_array = [];
        $patients = json_decode($request->input('patients'));

        foreach ($patients as $key => $value) {
            $p = Viralpatient::existing($value->facility_id, $value->patient)->first();
            if($p){
                // $patients_array[] = ['original_id' => $p->original_patient_id, 'national_patient_id' => $p->id ];
                $patients_array[] = ['original_id' => $value->id, 'national_patient_id' => $p->id ];
                continue;
            }


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
        $errors = [];
        
        $batches = json_decode($request->input('batches'));
        $lab_id = json_decode($request->input('lab_id'));

        foreach ($batches as $key => $value) {
            try {
                $batch = Viralbatch::where(['original_batch_id' => $value->id, 'lab_id' => $value->lab_id])->first();
                if(!$batch) $batch = new Viralbatch;
                $batch->original_batch_id = $value->id;
                $samples = $value->sample;
                $temp = $value;
                unset($temp->sample);
                unset($temp->id);
                $batch->fill(get_object_vars($temp));
                unset($batch->national_batch_id);
                $batch->save();

                $batches_array[] = ['original_id' => $batch->original_batch_id, 'national_batch_id' => $batch->id ];

                foreach ($samples as $key2 => $value2) {
                    // if($value2->parentid != 0) continue;

                    $sample = null;

                    // if($value2->national_sample_id){
                    //     $sample = Viralsample::find($value2->national_sample_id);
                    //     if($sample && $sample->original_sample_id != $value2->id){
                    //         $sample = null;
                    //     }
                    //     // {
                    //     //     $sample->delete();
                    //     //     unset($sample);
                    //     // }
                    // }

                    $sample_view = ViralsampleView::where(['original_sample_id' => $value2->id, 'lab_id' => $batch->lab_id])->get();
                    if($sample_view->count() == 1) $sample = Viralsample::find($sample_view->first()->id);
                    else{
                        foreach ($sample_view as $duplicate) {
                            $dup = Viralsample::find($duplicate->id);
                            $dup->delete();
                        }
                    }

                    if(!$sample) $sample = new Viralsample;


                    // if($value2->national_sample_id) $sample = Viralsample::find($value2->national_sample_id);
                    // else{
                    //     $sample = new Viralsample;
                    // }
                    $sample->fill(get_object_vars($value2));
                    $sample->original_sample_id = $sample->id;
                    $sample->patient_id = $value2->patient->national_patient_id;

                    if(!$sample->patient_id){
                        return response()->json([
                            'status' => 'not ok',
                            'sample' => $value2,
                        ], 400);
                    }
                
                    unset($sample->id);
                    unset($sample->patient);
                    unset($sample->national_sample_id);
                    unset($sample->sample_received_by);
                    unset($sample->areaname);
                    unset($sample->label_id);

                    $sample->batch_id = $batch->id;
                    $sample->save();
                    
                    $samples_array[] = ['original_id' => $sample->original_sample_id, 'national_sample_id' => $sample->id ];               
                }
                
            } catch (Exception $e) {
                $errors[] = ['message' => $e->getMessage(), 'line' => $e->getLine()];  
            }

            // Parent ID will be the sample ID at the lab instead of the national sample ID
            // foreach ($value->sample as $key2 => $value2) {
            //     if($value2->parentid == 0) continue;
            //     $sample = new Viralsample;
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
            'errors' => $errors,
        ], 201);
    }

    public function worksheets(BlankRequest $request)
    {
        $worksheets_array = [];
        $worksheets = json_decode($request->input('worksheets'));

        foreach ($worksheets as $key => $value) {
            $worksheet = Viralworksheet::where(['original_worksheet_id' => $value->id, 'lab_id' => $value->lab_id])->first();
            if(!$worksheet) $worksheet = new Viralworksheet;
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



    /*public function update_patients(BlankRequest $request)
    {
        $patients_array = [];
        $patients = json_decode($request->input('patients'));

        foreach ($patients as $key => $value) {
            if($value->national_patient_id){
                $patient = Viralpatient::find($value->national_patient_id);
            }else{
                $patient = Viralpatient::where(['original_patient_id' => $value->id, 'created_at' => $value->created_at])->get()->first();
            }

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

    public function update_batches(BlankRequest $request)
    {
        $batches_array = [];
        $batches = json_decode($request->input('batches'));
        $lab_id = json_decode($request->input('lab_id'));

        foreach ($batches as $key => $value) {
            if($value->national_batch_id){
                $batch = Viralbatch::find($value->national_batch_id);
            }else{
                $batch = Viralbatch::where(['original_batch_id' => $value->id, 'lab_id' => $value->lab_id])->get()->first();
            }

            $batch->fill(get_object_vars($value));
            $batch->original_batch_id = $batch->id;
            unset($batch->id);
            unset($batch->national_batch_id);
            $batch->save();
            $batches_array[] = ['original_id' => $batch->original_batch_id, 'national_batch_id' => $batch->id ];
        }

        return response()->json([
            'status' => 'ok',
            'batches' => $batches_array,
        ], 201);        
    }

    public function update_samples(BlankRequest $request)
    {
        $samples_array = [];
        $samples = json_decode($request->input('samples'));
        $lab_id = json_decode($request->input('lab_id'));

        foreach ($samples as $key => $value) {
            if($value->national_sample_id){
                $sample = Viralsample::find($value->national_sample_id);
            }else{
                $sample = Viralsample::where(['original_sample_id' => $value->id, 'lab_id' => $value->lab_id])->get()->first();
            }

            $sample->fill(get_object_vars($value));
            $sample->original_sample_id = $sample->id;
            unset($sample->id);
            unset($sample->national_sample_id);
            $sample->save();
            $samples_array[] = ['original_id' => $sample->original_sample_id, 'national_sample_id' => $sample->id ];
        }

        return response()->json([
            'status' => 'ok',
            'samples' => $samples_array,
        ], 201);        
    }*/

    public function update_patients(BlankRequest $request){
        return $this->update_dash($request, Viralpatient::class, 'patients', 'national_patient_id', 'original_patient_id');
    }

    public function update_batches(BlankRequest $request){
        return $this->update_dash($request, Viralbatch::class, 'batches', 'national_batch_id', 'original_batch_id');
    }

    public function update_samples(BlankRequest $request){
        return $this->update_dash($request, Viralsample::class, 'samples', 'national_sample_id', 'original_sample_id');
    }

    public function update_worksheets(BlankRequest $request){
        return $this->update_dash($request, Viralworksheet::class, 'worksheets', 'national_worksheet_id', 'original_worksheet_id');
    }

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
                    $s = \App\ViralsampleView::locate($value, $lab_id)->first();
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
                unset($update_data['areaname']);
                unset($update_data['label_id']);
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

    public function delete_patients(BlankRequest $request){
        return $this->delete_dash($request, Viralpatient::class, 'patients', 'national_patient_id', 'original_patient_id');
    }

    public function delete_batches(BlankRequest $request){
        return $this->delete_dash($request, Viralbatch::class, 'batches', 'national_batch_id', 'original_batch_id');
    }

    public function delete_samples(BlankRequest $request){
        return $this->delete_dash($request, Viralsample::class, 'samples', 'national_sample_id', 'original_sample_id');
    }


    public function delete_dash(BlankRequest $request, $update_class, $input, $nat_column, $original_column)
    {
        $models_array = [];
        $models = json_decode($request->input($input));
        $lab_id = json_decode($request->input('lab_id'));

        foreach ($models as $key => $value) {
            if($value->$nat_column){
                $new_model = $update_class::find($value->$nat_column);
            }else{
                if($input == 'samples'){
                    $s = \App\ViralsampleView::locate($value, $lab_id)->first();
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
