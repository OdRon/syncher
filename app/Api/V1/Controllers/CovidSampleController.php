<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Api\V1\Requests\BlankRequest;


use App\CovidWorksheet;
use App\CovidPatient;
use App\CovidSample;
use App\CovidTravel;
use App\Facility;
use DB;

class CovidSampleController extends Controller
{
  
    public function index(BlankRequest $request)
    {

    }

    
    public function store(BlankRequest $request)
    {
        $s =  json_decode($request->input('sample'));
        $p = $s->patient;
        unset($p->travel);
        if($p->national_patient_id){
            $patient = CovidPatient::find($p->national_patient_id);
        }else{
            $patient = new CovidPatient;
        }
        $patient_array = get_object_vars($p);
        $patient->original_patient_id = $p->id;
        $patient->fill($patient_array);
        $patient->save();

        $children = $s->child ?? [];
        unset($s->patient);
        unset($s->child);
        $sample_array = get_object_vars($s);

        if($s->national_sample_id){
            $sample = CovidSample::find($s->national_sample_id);
        }else{
            $sample = new CovidSample;
        }
        if(!$sample) $sample = new CovidSample;
        $sample->fill($sample_array);
        $sample->original_sample_id = $s->id;
        $sample->save();
        // $sample_data[0] = ['original_id' => $s->id, 'national_id' => $sample->id];
        $sample_data[$s->id] = $sample->id;

        foreach ($children as $key => $child) {

            $child_sample = new CovidSample;
            $child_sample->fill(get_object_vars($child));
            $child_sample->cif_sample_id = $sample->cif_sample_id;
            $child_sample->original_sample_id = $child->id;
            $child_sample->save();
            // $sample_data[] = ['original_id' => $child->id, 'national_id' => $child_sample->id];
            $sample_data[$child->id] = $child_sample->id;
        }

        return response()->json([
            'status' => 'ok',
            'patient' => $patient->id,
            'sample' => $sample_data,
        ], 201);        
    }


    /**
     * Display the specified resource.
     *
     * @Get("/{id}")
     * @Response(200, body={
     *      "sample": {
     *          "id": "int",    
     *          "patient": {
     *              "id": "int",
     *          }    
     *      }
     * })
     */
    public function show($id)
    {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Facility  $facility
     * @return \Illuminate\Http\Response
     */
    public function update(BlankRequest $request, $id=null)
    {
        $s = $request->input('sample');
        $sample = CovidSample::findOrFail($s->id);
        $sample_array = get_object_vars($s);
        unset($sample_array['id']);
        unset($sample_array['national_sample_id']);
        unset($sample_array['created_at']);
        unset($sample_array['updated_at']);
        unset($sample_array['patient_id']);
        $sample->fill($sample_array);
        $sample->synched = 2;
        $sample->date_synched = date('Y-m-d');
        $sample->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Facility  $facility
     * @return \Illuminate\Http\Response
     */
    public function destroy(Facility $facility)
    {
        //
    }


    public function update_samples(BlankRequest $request){
        return $this->update_dash($request, CovidSample::class, 'samples', 'national_sample_id', 'original_sample_id');
    }

    public function update_patients(BlankRequest $request){
        return $this->update_dash($request, CovidPatient::class, 'patients', 'national_patient_id', 'original_patient_id');
    }

    public function update_worksheets(BlankRequest $request){
        return $this->update_dash($request, CovidWorksheet::class, 'worksheets', 'national_worksheet_id', 'original_worksheet_id');
    }

    public function update_dash(BlankRequest $request, $update_class, $input, $nat_column, $original_column)
    {
        $models_array = [];
        $errors_array = [];
        $models = json_decode($request->input($input));
        $lab_id = json_decode($request->input('lab_id'));

        foreach ($models as $key => $value) {
            if($value->$nat_column){
                $updating_model = $update_class::find($value->$nat_column);
            }else{
                if($input == 'samples'){
                    $s = \App\CovidSample::where([$original_column => $value, 'lab_id' => $lab_id])->first();
                    if(!$s){
                        $errors_array[] = $value;
                        continue;
                    }
                    $updating_model = $update_class::find($s->id);
                }else{
                    $updating_model = $update_class::locate($value)->get()->first();
                }
            }

            if(!$updating_model){
                $errors_array[] = $value;
                continue;
            }

            $update_data = get_object_vars($value);
            unset($update_data['id']);
            unset($update_data['created_at']);
            unset($update_data['updated_at']);

            if($input == 'samples'){
                // $original_patient = $value->patient;
                // $update_data['patient_id'] = $original_patient->national_patient_id;
                // unset($update_data['patient']);
            }

            $updating_model->fill($update_data);
            $updating_model->$original_column = $value->id;
            $updating_model->synched = 2;
            unset($updating_model->$nat_column);
            $updating_model->save();
            $models_array[] = ['original_id' => $updating_model->$original_column, $nat_column => $updating_model->id ];
        }

        if(count($errors_array) == 0) $errors_array = null;

        return response()->json([
            'status' => 'ok',
            $input => $models_array,
            'errors_array' => $errors_array,
        ], 201);        
    }


}

