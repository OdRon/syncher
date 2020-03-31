<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Api\V1\Requests\BlankRequest;


use App\CovidPatient;
use App\CovidSample;
use App\CovidTravel;
use App\Facility;
use DB;

/**
 * Covid Controller resource representation.
 * @Parameters({
 *      @Parameter("id", description="The id of the sample.", type="integer", required=true),
 * })
 *
 * @Resource("Covid", uri="/covid")
 */
class CovidController extends Controller
{
  
    /**
     * Display a listing of the resource.
     * The response has links to navigate to the rest of the data.
     *
     *
     * @Get("{?page}")
     * @Response(200, body={
     *      "data": {
     *          "sample": {
     *              "id": "int",    
     *              "patient": {
     *                  "id": "int",
     *              }    
     *          }
     *      }
     * })
     */
    public function index()
    {
        return CovidSample::with(['patient'])->where('repeatt', 0)->paginate();
    }

    
    /**
     * Register a resource.
     *
     * @Post("/")
     * @Request({
     *      "case_id": "int, case number", 
     *      "identifier_type": "int, identifier type", 
     *      "identifier": "string, actual identifier, National ID... ", 
     *      "patient_name": "string", 
     *      "justification": "int, reason for the test", 
     *      "facility": "string, MFL Code or DHIS Code of the facility if any", 
     *      "county": "string", 
     *      "subcounty": "string", 
     *      "ward": "string", 
     *      "residence": "string", 
     *      "sex": "string, M for male, F for female", 
     *      "health_status": "int, health status", 
     *      "residence": "string", 
     *      "date_symptoms": "date", 
     *      "date_admission": "date", 
     *      "date_isolation": "date", 
     *      "date_death": "date", 
     *      
     *      "lab_id": "int, refer to ref tables", 
     *      "test_type": "int", 
     *      "occupation": "string", 
     *      "temperature": "int, temp in Celcius", 
     *      "sample_type": "int, refer to ref tables", 
     *      "symptoms": "array of integers, refer to ref tables", 
     *      "observed_signs": "array of integers, refer to ref tables", 
     *      "underlying_conditions": "array of integers, refer to ref tables", 
     * })
     * @Response(201)
     */
    public function store(BlankRequest $request)
    {
        $p = new CovidPatient;
        $p->fill($request->only(['case_id', 'identifier_type_id', 'identifier', 'patient_name', 'justification', 'county', 'subcounty', 'ward', 'residence', 'dob', 'sex', 'occupation', 'health_status', 'date_symptoms', 'date_admission', 'date_isolation', 'date_death']));
        $p->facility_id = Facility::locate($request->input('facility'))->first()->id ?? '';
        $p->save();

        $s = new CovidSample;
        $s->fill($request->only(['lab_id', 'test_type', 'health_status', 'symptoms', 'temperature', 'observed_signs', 'underlying_conditions', ]));
        $s->patient_id = $p->id;
        $s->save();

        return response()->json([
          'status' => 'ok',
          'patient' => $p,
          'sample' => $s,
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
        $s = CovidSample::find($id);
        $s->load(['patient']);

        return response()->json([
          'sample' => $s,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Facility  $facility
     * @return \Illuminate\Http\Response
     */
    public function update(BlankRequest $request, $id)
    {
        
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


    
    /**
     * Register multiple resources.
     *
     * @Post("/")
     * @Request({
     *      "sample": {{
     *      "case_id": "int, case number", 
     *      "identifier_type": "int, identifier type", 
     *      "identifier": "string, actual identifier, National ID... ", 
     *      "patient_name": "string", 
     *      "justification": "int, reason for the test", 
     *      "facility": "string, MFL Code or DHIS Code of the facility if any", 
     *      "county": "string", 
     *      "subcounty": "string", 
     *      "ward": "string", 
     *      "residence": "string", 
     *      "sex": "string, M for male, F for female", 
     *      "health_status": "int, health status", 
     *      "residence": "string", 
     *      "date_symptoms": "date", 
     *      "date_admission": "date", 
     *      "date_isolation": "date", 
     *      "date_death": "date", 
     *      
     *      "lab_id": "int, refer to ref tables", 
     *      "test_type": "int", 
     *      "occupation": "string", 
     *      "temperature": "int, temp in Celcius", 
     *      "sample_type": "int, refer to ref tables", 
     *      "symptoms": "array of integers, refer to ref tables", 
     *      "observed_signs": "array of integers, refer to ref tables", 
     *      "underlying_conditions": "array of integers, refer to ref tables", 
     *      }}
     * })
     * @Response(201)
     */
    public function multiple(BlankRequest $request)
    {
        $samples = $request->input('samples');
        $patients = $samples = [];

        foreach ($samples as $key => $row) {
            $row = collect($row);

            $p = new CovidPatient;
            $p->fill($request->only(['case_id', 'identifier_type_id', 'identifier', 'patient_name', 'justification', 'county', 'subcounty', 'ward', 'residence', 'dob', 'sex', 'occupation', 'health_status', 'date_symptoms', 'date_admission', 'date_isolation', 'date_death']));
            $p->facility_id = Facility::locate($request->input('facility'))->first()->id ?? '';
            $p->save();

            $patients[] = $p;

            $s = new CovidSample;
            $s->fill($request->only(['lab_id', 'test_type', 'health_status', 'symptoms', 'temperature', 'observed_signs', 'underlying_conditions', ]));
            $s->patient_id = $p->id;
            $s->save();

            $samples[] = $s;
        }

        return response()->json([
          'status' => 'ok',
          'patient' => $patients,
          'sample' => $samples,
        ], 201);
    }

}

