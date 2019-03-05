<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Api\V1\Requests\BlankRequest;

use App\Jobs\NewFacility;

use App\Facility;
use DB;

class FacilityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(BlankRequest $request)
    {
        $facilities = json_decode($request->input('facilities'));

        $facility_data = [];

        foreach ($facilities as $key => $value) {
            $fac = Facility::where(['facilitycode' => $value->facilitycode])->first();

            if(!$fac){
                $fac = new Facility;
                $fac->fill(get_object_vars($value));
                $fac->synched = 1;
                $fac->save();

                $fac_array = $fac->toArray();
                unset($fac_array['poc']);
                unset($fac_array['has_gene']);
                unset($fac_array['has_alere']);

                DB::table("apidb.facilitys")->insert($fac_array);
            }

            $facility_data[] = ['old_facility_id' => $value->id, 'new_facility_id' => $fac->id];
        }

        // $lab_id = $request->input('lab_id');
        // $facility = new Facility;
        // $facility->fill($facility_data);
        // // unset($facility->id);
        // $facility->save();

        // NewFacility::dispatch($facility);

        return response()->json([
          'status' => 'ok',
          'facilities' => $facility_data,
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Facility  $facility
     * @return \Illuminate\Http\Response
     */
    public function show(Facility $facility)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Facility  $facility
     * @return \Illuminate\Http\Response
     */
    public function update(BlankRequest $request, Facility $facility)
    {
        $data = json_decode($request->input('facility'));
        $facility->fill($data);
        $facility->save();

        NewFacility::dispatch($facility, true);

        return response()->json([
          'status' => 'ok',
        ], 200);
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
}

